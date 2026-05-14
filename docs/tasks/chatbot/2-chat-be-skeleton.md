# 13 — Dija: Backend Skeleton

> **Backlog ref:** Chatbot Phase 1 — BE skeleton
> **Priority:** P1 — must come after task 12 is merged
> **Effort:** ~3h
> **Stack:** Laravel 11, Sanctum, SSE
> **Branch:** `<yourname>/chat-skeleton` (example: `ornela/chat-skeleton`)
> **Before you start:** verify task 12 is merged (`php artisan migrate:status` should show all `chat_*` tables as `Ran`). Read `docs/chatbot-plan.md` §2, §4, §6, §8 to understand the architecture — this task is Phase 1 of that plan.

---

## Goal

Stand up the full backend structure for Dija **without touching the real LLM yet**. When this task is done:

- All conversation CRUD endpoints (`POST /chat/conversations`, `GET`, `DELETE`) work and persist to the DB.
- Sending a message returns a real SSE stream — but the "assistant" is `FakeChatProvider`, which just echoes the user's message back. This lets the frontend task start in parallel without waiting for Anthropic integration.
- The architecture is in place: `ChatController → ChatService → ChatProvider → ToolDispatcher`.

**Do not** integrate the Anthropic API. Do not add tool logic. Do not touch the frontend. That is task 14 (LLM) and task 15 (tools).

---

## Workflow

1. `git checkout main && git pull` (after task 12 is merged)
2. `git checkout -b <yourname>/chat-skeleton`
3. Build in order: interface → fake provider → service → controller → routes
4. Commit per logical step (`chat-provider-interface`, `fake-provider`, `chat-service`, `chat-controller`, `chat-routes`)
5. `make fix` before each commit, `make ci` before pushing
6. Open PR against `main`, request review from `kristopapallazo`
7. Link this doc in the PR description

---

## Step 0 — .env vars

Add to `.env` and `.env.example`:

```
ANTHROPIC_API_KEY=sk-ant-placeholder
CHATBOT_MODEL=claude-haiku-4-5-20251001
CHATBOT_DAILY_TOKEN_LIMIT=50000
VOYAGE_API_KEY=pa-placeholder
```

Add to `config/services.php`:

```php
'anthropic' => [
    'key'             => env('ANTHROPIC_API_KEY'),
    'model'           => env('CHATBOT_MODEL', 'claude-haiku-4-5-20251001'),
    'daily_token_limit' => (int) env('CHATBOT_DAILY_TOKEN_LIMIT', 50000),
],
```

---

## Step 1 — ChatProvider interface

**File:** `app/Services/Chat/ChatProvider.php`

```php
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
     * @param  callable(string $token): void  $onToken   called for each streamed token
     * @param  callable(string $name, array $input): array  $onToolCall  called when LLM requests a tool
     * @return ChatResult  final token counts once stream is complete
     */
    public function stream(
        array $messages,
        callable $onToken,
        callable $onToolCall,
    ): ChatResult;
}
```

---

## Step 2 — FakeChatProvider

**File:** `app/Services/Chat/FakeChatProvider.php`

Echoes the last user message back, word by word, with a small delay so SSE can be tested end-to-end.

```php
<?php

namespace App\Services\Chat;

class FakeChatProvider implements ChatProvider
{
    public function stream(
        array $messages,
        callable $onToken,
        callable $onToolCall,
    ): ChatResult {
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
```

---

## Step 3 — ChatService

**File:** `app/Services/Chat/ChatService.php`

Orchestrates one conversation turn: load history → call provider → persist messages → update usage.

```php
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
     * Persists user + assistant messages and updates daily usage after stream ends.
     */
    public function sendMessage(
        ChatConversation $conversation,
        User $user,
        string $userContent,
        callable $onToken,
    ): void {
        // Build message history for the LLM
        $history = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->toArray();

        $history[] = ['role' => 'user', 'content' => $userContent];

        // Stream from provider
        $fullReply = '';
        $result = $this->provider->stream(
            messages: $history,
            onToken: function (string $token) use (&$fullReply, $onToken) {
                $fullReply .= $token;
                $onToken($token);
            },
            onToolCall: fn (string $name, array $input) => [], // tools in task 15
        );

        // Persist both messages and update quota — all in one transaction
        DB::transaction(function () use ($conversation, $userContent, $fullReply, $result, $user) {
            $userMsg = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role'            => 'user',
                'content'         => $userContent,
                'token_count'     => $result->inputTokens,
                'created_at'      => now(),
            ]);

            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role'            => 'assistant',
                'content'         => $fullReply,
                'token_count'     => $result->outputTokens,
                'created_at'      => now()->addSecond(),
            ]);

            $conversation->update(['last_msg_at' => now()]);

            // Upsert daily usage — increment atomically
            ChatUsageDaily::upsert(
                [
                    'user_id'    => $user->id,
                    'day'        => now()->toDateString(),
                    'tokens_in'  => $result->inputTokens,
                    'tokens_out' => $result->outputTokens,
                    'messages'   => 1,
                ],
                uniqueBy: ['user_id', 'day'],
                update:   ['tokens_in', 'tokens_out', 'messages'],
            );
        });
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
```

---

## Step 4 — ChatController

**File:** `app/Http/Controllers/ChatController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedCollection;
use App\Http\Traits\ApiResponse;
use App\Models\ChatConversation;
use App\Models\ChatUsageDaily;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ChatService $service) {}

    /** List the authenticated user's conversations. */
    public function indexConversations(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 20), 100);

        $conversations = ChatConversation::where('user_id', $request->user()->id)
            ->orderByDesc('last_msg_at')
            ->paginate($perPage);

        return (new PaginatedCollection($conversations->through(fn ($c) => [
            'id'        => $c->id,
            'title'     => $c->title,
            'lastMsgAt' => $c->last_msg_at?->toIso8601String(),
            'startedAt' => $c->started_at->toIso8601String(),
        ])))->response();
    }

    /** Start a new conversation. */
    public function storeConversation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:200',
        ]);

        $conversation = ChatConversation::create([
            'user_id'   => $request->user()->id,
            'user_role' => $request->user()->role,
            'title'     => $data['title'] ?? null,
            'started_at'=> now(),
        ]);

        return $this->success(['id' => $conversation->id], 'Biseda u krijua me sukses.', 201);
    }

    /** Get full message history for a conversation. */
    public function showConversation(Request $request, int $id): JsonResponse
    {
        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id'         => $m->id,
                'role'       => $m->role,
                'content'    => $m->content,
                'tokenCount' => $m->token_count,
                'createdAt'  => $m->created_at->toIso8601String(),
                'toolCalls'  => $m->toolCalls->map(fn ($t) => [
                    'toolName'   => $t->tool_name,
                    'input'      => $t->input_json,
                    'output'     => $t->output_json,
                    'durationMs' => $t->duration_ms,
                    'status'     => $t->status,
                ]),
            ]);

        return $this->success([
            'conversation' => [
                'id'        => $conversation->id,
                'title'     => $conversation->title,
                'startedAt' => $conversation->started_at->toIso8601String(),
            ],
            'messages' => $messages,
        ], 'OK');
    }

    /** Delete a conversation and all its messages. */
    public function destroyConversation(Request $request, int $id): JsonResponse
    {
        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $conversation->delete();

        return $this->success(null, 'Biseda u fshi me sukses.');
    }

    /**
     * Send a user message — returns an SSE stream of assistant tokens.
     *
     * SSE event format:
     *   data: {"type":"token","content":"..."}\n\n
     *   data: {"type":"done"}\n\n
     *   data: {"type":"error","message":"..."}\n\n
     */
    public function sendMessage(Request $request, int $id): StreamedResponse
    {
        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $data = $request->validate([
            'content' => 'required|string|max:4000',
        ]);

        if ($this->service->isOverQuota($request->user())) {
            return response()->stream(function () {
                $this->sseEvent(['type' => 'error', 'message' => 'Keni arritur limitin ditor. Provoni nesër.']);
            }, 200, $this->sseHeaders());
        }

        return response()->stream(function () use ($conversation, $request, $data) {
            $this->service->sendMessage(
                conversation: $conversation,
                user: $request->user(),
                userContent: $data['content'],
                onToken: fn (string $token) => $this->sseEvent(['type' => 'token', 'content' => $token]),
            );

            $this->sseEvent(['type' => 'done']);
        }, 200, $this->sseHeaders());
    }

    /** Today's token usage for the authenticated user. */
    public function usage(Request $request): JsonResponse
    {
        $limit = config('services.anthropic.daily_token_limit', 50_000);

        $usage = ChatUsageDaily::where('user_id', $request->user()->id)
            ->where('day', now()->toDateString())
            ->first();

        return $this->success([
            'tokensIn'   => $usage?->tokens_in  ?? 0,
            'tokensOut'  => $usage?->tokens_out ?? 0,
            'messages'   => $usage?->messages   ?? 0,
            'dailyLimit' => $limit,
        ], 'OK');
    }

    private function sseHeaders(): array
    {
        return [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ];
    }

    private function sseEvent(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";
        ob_flush();
        flush();
    }
}
```

---

## Step 5 — Wire into AppServiceProvider

`ChatService` depends on `ChatProvider`. Bind the interface to `FakeChatProvider` for now (replaced in task 14 with `AnthropicChatProvider`).

Open `app/Providers/AppServiceProvider.php`, add inside `register()`:

```php
use App\Services\Chat\ChatProvider;
use App\Services\Chat\FakeChatProvider;

$this->app->bind(ChatProvider::class, FakeChatProvider::class);
```

---

## Step 6 — Add routes to api.php

Add the import at the top:

```php
use App\Http\Controllers\ChatController;
```

Inside the `Route::middleware('auth:sanctum')->group(...)` block, add:

```php
// ── Dija chat ────────────────────────────────────────────────────
Route::prefix('chat')->group(function () {
    Route::get('/conversations', [ChatController::class, 'indexConversations']);
    Route::post('/conversations', [ChatController::class, 'storeConversation']);
    Route::get('/conversations/{id}', [ChatController::class, 'showConversation']);
    Route::delete('/conversations/{id}', [ChatController::class, 'destroyConversation']);
    Route::post('/conversations/{id}/messages', [ChatController::class, 'sendMessage']);
    Route::get('/usage', [ChatController::class, 'usage']);
});
```

---

## Manual smoke test

After `make dev`:

```bash
# 1. Login and grab token
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test.student@students.uamd.edu.al","password":"password"}' \
  | jq -r '.data.token')

# 2. Create a conversation
CONV_ID=$(curl -s -X POST http://localhost:8000/api/v1/chat/conversations \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test bisede"}' | jq -r '.data.id')

# 3. Send a message — watch SSE stream
curl -N -X POST http://localhost:8000/api/v1/chat/conversations/$CONV_ID/messages \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content":"Kur e kam orën tjetër?"}'
# Expected: data: {"type":"token","content":"[Dija (fake)] Echo: ..."} events
# then: data: {"type":"done"}

# 4. Check history
curl -s http://localhost:8000/api/v1/chat/conversations/$CONV_ID \
  -H "Authorization: Bearer $TOKEN" | jq '.data.messages | length'
# Expected: 2 (user + assistant)
```

---

## Acceptance criteria

- [ ] `POST /api/v1/chat/conversations` creates a conversation, returns `{ id }`
- [ ] `GET /api/v1/chat/conversations` returns paginated list for the authenticated user only
- [ ] `GET /api/v1/chat/conversations/{id}` returns messages with `toolCalls` array (empty for now)
- [ ] `DELETE /api/v1/chat/conversations/{id}` deletes conversation + cascades to messages
- [ ] `POST /api/v1/chat/conversations/{id}/messages` returns an SSE stream with `token` events and a final `done` event
- [ ] Two `chat_messages` rows are written to the DB after a message is sent (user + assistant)
- [ ] `GET /api/v1/chat/usage` returns `{ tokensIn, tokensOut, messages, dailyLimit }`
- [ ] A user cannot access another user's conversation (returns 404)
- [ ] `make ci` passes
