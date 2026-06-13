<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Real LLM provider — streams Claude's reply from the Anthropic Messages API.
 *
 * Per plan §11.2 we call the API with Laravel's built-in Http:: (Guzzle), no SDK:
 * one streaming POST, then we parse the SSE body ourselves. Tools/RAG arrive in
 * task 15 — until then $tools is empty and $onToolCall is never invoked.
 */
class AnthropicChatProvider implements ChatProvider
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $systemPrompt,
    ) {}

    public function stream(
        array $messages,
        array $tools,
        callable $onToken,
        callable $onToolCall,
    ): ChatResult {
        // $tools / $onToolCall are unused until task 15.

        $payload = [
            'model' => $this->model,
            'max_tokens' => 1024,
            // System prompt as a cacheable block. Caching only kicks in once the
            // prefix (system + tools) exceeds Haiku's 4096-token minimum, which
            // won't happen until tools/RAG land in task 15 — harmless to set now.
            'system' => [[
                'type' => 'text',
                'text' => $this->systemPrompt,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'messages' => array_values($messages),
            'stream' => true,
        ];

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])->withOptions(['stream' => true])
            ->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic API error: HTTP ' . $response->status());
        }

        return $this->consumeStream($response->toPsrResponse()->getBody(), $onToken);
    }

    /**
     * Read the SSE body chunk by chunk, dispatch text deltas, and collect token counts.
     *
     * @param  StreamInterface  $body
     */
    private function consumeStream($body, callable $onToken): ChatResult
    {
        $buffer = '';
        $fullText = '';
        $inputTokens = 0;
        $outputTokens = 0;

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            // SSE events are separated by a blank line ("\n\n").
            while (($sep = strpos($buffer, "\n\n")) !== false) {
                $rawEvent = substr($buffer, 0, $sep);
                $buffer = substr($buffer, $sep + 2);

                foreach (explode("\n", $rawEvent) as $line) {
                    if (! str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $json = json_decode(trim(substr($line, 5)), true);
                    if (! is_array($json)) {
                        continue;
                    }

                    switch ($json['type'] ?? '') {
                        case 'message_start':
                            // Initial usage: prompt (and cache) input tokens.
                            $inputTokens = (int) ($json['message']['usage']['input_tokens'] ?? 0);
                            break;

                        case 'content_block_delta':
                            if (($json['delta']['type'] ?? '') === 'text_delta') {
                                $token = $json['delta']['text'] ?? '';
                                $fullText .= $token;
                                $onToken($token);
                            }
                            break;

                        case 'message_delta':
                            // Cumulative output token count lands here.
                            $outputTokens = (int) ($json['usage']['output_tokens'] ?? $outputTokens);
                            break;

                        case 'error':
                            throw new RuntimeException(
                                'Anthropic stream error: ' . ($json['error']['message'] ?? 'unknown')
                            );
                    }
                }
            }
        }

        return new ChatResult(
            content: $fullText,
            inputTokens: $inputTokens,
            outputTokens: $outputTokens,
        );
    }
}
