<?php

namespace App\Services\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatUsageDaily;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(private readonly ChatProvider $provider) {}

    /**
     * Stream one turn. Calls $onToken for each assistant token.
     * Persists user + assistant messages and updates daily usage after the stream ends.
     */
    public function sendMessage(
        ChatConversation $conversation,
        User $user,
        string $userContent,
        callable $onToken,
    ): void {
        // Build message history for the LLM.
        // Order by `id`, not `created_at`: two messages in the same turn can share a
        // second-granularity timestamp, but the auto-increment id is always monotonic.
        $history = $conversation->messages()
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $history[] = ['role' => 'user', 'content' => $userContent];

        // Stream from provider ($tools is empty until task 15).
        $fullReply = '';
        $result = $this->provider->stream(
            messages: $history,
            tools: [],
            onToken: function (string $token) use (&$fullReply, $onToken) {
                $fullReply .= $token;
                $onToken($token);
            },
            onToolCall: fn (string $name, array $input) => [], // tools in task 15
        );

        // Persist both messages and update quota — all in one transaction.
        DB::transaction(function () use ($conversation, $userContent, $fullReply, $result, $user) {
            // Insert user first, then assistant. Ordering is guaranteed by the
            // auto-increment id (see the history query above), so both rows can
            // safely share the same created_at — no addSecond() hack needed.
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'user',
                'content' => $userContent,
                'token_count' => $result->inputTokens,
                'created_at' => now(),
            ]);

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $fullReply,
                'token_count' => $result->outputTokens,
                'created_at' => now(),
            ]);

            $conversation->update(['last_msg_at' => now()]);

            $this->accumulateUsage($user, $result->inputTokens, $result->outputTokens);
        });
    }

    /**
     * Add this turn's tokens to the user's daily row. Must *increment*, not overwrite —
     * Eloquent's upsert() would replace the totals and the quota would never trip.
     * Done as a portable update-then-insert (works on SQLite and MySQL) rather than
     * a MySQL-only INSERT ... ON DUPLICATE KEY UPDATE. Runs inside the caller's
     * transaction; token counts are ints, so the raw arithmetic is injection-safe.
     */
    private function accumulateUsage(User $user, int $tokensIn, int $tokensOut): void
    {
        $today = now()->toDateString();

        $updated = DB::table('chat_usage_daily')
            ->where('user_id', $user->id)
            ->where('day', $today)
            ->update([
                'tokens_in' => DB::raw('tokens_in + ' . (int) $tokensIn),
                'tokens_out' => DB::raw('tokens_out + ' . (int) $tokensOut),
                'messages' => DB::raw('messages + 1'),
            ]);

        if ($updated === 0) {
            DB::table('chat_usage_daily')->insert([
                'user_id' => $user->id,
                'day' => $today,
                'tokens_in' => $tokensIn,
                'tokens_out' => $tokensOut,
                'messages' => 1,
            ]);
        }
    }

    /** Check if today's quota is exceeded. */
    public function isOverQuota(User $user): bool
    {
        $limit = config('services.anthropic.daily_token_limit', 50_000);

        $usage = ChatUsageDaily::where('user_id', $user->id)
            ->where('day', now()->toDateString())
            ->first();

        if (! $usage) {
            return false;
        }

        return ($usage->tokens_in + $usage->tokens_out) >= $limit;
    }
}
