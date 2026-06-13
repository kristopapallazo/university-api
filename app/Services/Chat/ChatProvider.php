<?php

namespace App\Services\Chat;

class ChatResult
{
    public function __construct(
        public readonly string $content,
        public readonly int $inputTokens,
        public readonly int $outputTokens,
    ) {}
}

interface ChatProvider
{
    /**
     * Stream a reply to a conversation turn.
     *
     * @param  array  $messages  [['role' => 'user'|'assistant', 'content' => string], ...]
     * @param  array  $tools  tool schemas exposed to the LLM (empty until task 15)
     * @param  callable(string $token): void  $onToken  called for each streamed token
     * @param  callable(string $name, array $input, string $toolUseId): array  $onToolCall  called when the LLM requests a tool
     * @return ChatResult final token counts once the stream is complete
     */
    public function stream(
        array $messages,
        array $tools,
        callable $onToken,
        callable $onToolCall,
    ): ChatResult;
}
