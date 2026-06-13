<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicChatProvider implements ChatProvider
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    private const MAX_TOOL_ITERATIONS = 5;

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
        $totalInput = 0;
        $totalOutput = 0;
        $fullText = '';
        $history = $messages;

        for ($i = 0; $i < self::MAX_TOOL_ITERATIONS; $i++) {
            $payload = [
                'model' => $this->model,
                'max_tokens' => 1024,
                'system' => [[
                    'type' => 'text',
                    'text' => $this->systemPrompt,
                    'cache_control' => ['type' => 'ephemeral'],
                ]],
                'messages' => array_values($history),
                'stream' => true,
            ];

            if (! empty($tools)) {
                $payload['tools'] = $tools;
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => self::API_VERSION,
                'content-type' => 'application/json',
            ])->withOptions(['stream' => true])->post(self::ENDPOINT, $payload);

            if ($response->failed()) {
                throw new RuntimeException('Anthropic API error: HTTP ' . $response->status());
            }

            $parsed = $this->consumeStream($response->toPsrResponse()->getBody(), $onToken);

            $totalInput += $parsed['inputTokens'];
            $totalOutput += $parsed['outputTokens'];
            $fullText .= $parsed['text'];

            if ($parsed['stopReason'] !== 'tool_use' || empty($parsed['toolUses'])) {
                break;
            }

            // Build the assistant turn with both text (if any) and tool_use blocks
            $assistantContent = [];
            if ($parsed['text'] !== '') {
                $assistantContent[] = ['type' => 'text', 'text' => $parsed['text']];
            }
            foreach ($parsed['toolUses'] as $tu) {
                $assistantContent[] = [
                    'type' => 'tool_use',
                    'id' => $tu['id'],
                    'name' => $tu['name'],
                    'input' => empty($tu['input']) ? new \stdClass : $tu['input'],
                ];
            }
            $history[] = ['role' => 'assistant', 'content' => $assistantContent];

            // Execute tools and build the tool_result user turn
            $toolResults = [];
            foreach ($parsed['toolUses'] as $tu) {
                $result = $onToolCall($tu['name'], $tu['input'], $tu['id']);
                $toolResults[] = [
                    'type' => 'tool_result',
                    'tool_use_id' => $tu['id'],
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }
            $history[] = ['role' => 'user', 'content' => $toolResults];
        }

        return new ChatResult(
            content: $fullText,
            inputTokens: $totalInput,
            outputTokens: $totalOutput,
        );
    }

    /**
     * Parse the SSE body, emit text tokens, accumulate tool_use blocks.
     *
     * Returns an array with keys: text, toolUses, inputTokens, outputTokens, stopReason.
     */
    private function consumeStream(mixed $body, callable $onToken): array
    {
        $buffer = '';
        $text = '';
        $inputTokens = 0;
        $outputTokens = 0;
        $stopReason = 'end_turn';
        $blocks = []; // index => block data

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

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
                            $inputTokens = (int) ($json['message']['usage']['input_tokens'] ?? 0);
                            break;

                        case 'content_block_start':
                            $idx = $json['index'];
                            $block = $json['content_block'] ?? [];
                            if (($block['type'] ?? '') === 'tool_use') {
                                $blocks[$idx] = [
                                    'type' => 'tool_use',
                                    'id' => $block['id'] ?? '',
                                    'name' => $block['name'] ?? '',
                                    'input_json' => '',
                                ];
                            } else {
                                $blocks[$idx] = ['type' => 'text'];
                            }
                            break;

                        case 'content_block_delta':
                            $idx = $json['index'];
                            $delta = $json['delta'] ?? [];
                            if ($delta['type'] === 'text_delta') {
                                $token = $delta['text'] ?? '';
                                $text .= $token;
                                $onToken($token);
                            } elseif ($delta['type'] === 'input_json_delta') {
                                $blocks[$idx]['input_json'] .= $delta['partial_json'] ?? '';
                            }
                            break;

                        case 'message_delta':
                            $outputTokens = (int) ($json['usage']['output_tokens'] ?? $outputTokens);
                            $stopReason = $json['delta']['stop_reason'] ?? 'end_turn';
                            break;

                        case 'error':
                            throw new RuntimeException(
                                'Anthropic stream error: ' . ($json['error']['message'] ?? 'unknown')
                            );
                    }
                }
            }
        }

        $toolUses = [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'tool_use') {
                $toolUses[] = [
                    'id' => $block['id'],
                    'name' => $block['name'],
                    'input' => json_decode($block['input_json'] ?: '{}', true) ?? [],
                ];
            }
        }

        return [
            'text' => $text,
            'toolUses' => $toolUses,
            'inputTokens' => $inputTokens,
            'outputTokens' => $outputTokens,
            'stopReason' => $stopReason,
        ];
    }
}
