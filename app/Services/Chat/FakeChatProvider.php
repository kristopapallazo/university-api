<?php

namespace App\Services\Chat;

/**
 * Phase 1 stand-in for the real LLM. Echoes the last user message back word by
 * word so the SSE pipeline can be exercised end-to-end before task 14 wires
 * AnthropicChatProvider. Never calls a tool.
 */
class FakeChatProvider implements ChatProvider
{
    public function stream(
        array $messages,
        array $tools,
        callable $onToken,
        callable $onToolCall,
    ): ChatResult {
        // $tools and $onToolCall are ignored in Phase 1 — the fake never calls a tool.
        $lastUserContent = '';
        foreach (array_reverse($messages) as $msg) {
            if ($msg['role'] === 'user') {
                $lastUserContent = $msg['content'];
                break;
            }
        }

        $reply = '[Dija (fake)] Echo: ' . $lastUserContent;

        foreach (explode(' ', $reply) as $word) {
            $onToken($word . ' ');
            usleep(40_000); // 40 ms between words
        }

        return new ChatResult(
            content: $reply,
            inputTokens: str_word_count($lastUserContent),
            outputTokens: str_word_count($reply),
        );
    }
}
