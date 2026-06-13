<?php

namespace App\Http\Controllers;

use App\Http\Resources\PaginatedCollection;
use App\Http\Traits\ApiResponse;
use App\Models\ChatConversation;
use App\Models\ChatUsageDaily;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ChatService $service) {}

    /**
     * List conversations
     *
     * Returns the authenticated user's Dija conversations, newest activity first.
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @queryParam perPage integer optional Items per page (max 100). Example: 20
     *
     * @response 200 {
     *   "data": [{"id": 1, "title": "Pyetje për orarin", "lastMsgAt": "2026-06-07T09:12:00+00:00", "startedAt": "2026-06-07T09:10:00+00:00"}],
     *   "pagination": {"current": 1, "pageSize": 20, "total": 1},
     *   "message": "OK",
     *   "status": 200
     * }
     */
    public function indexConversations(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('perPage', 20), 100);

        $conversations = ChatConversation::where('user_id', $request->user()->id)
            ->orderByDesc('last_msg_at')
            ->paginate($perPage);

        return (new PaginatedCollection($conversations->through(fn ($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'lastMsgAt' => $c->last_msg_at?->toIso8601String(),
            'startedAt' => $c->started_at->toIso8601String(),
        ])))->response();
    }

    /**
     * Start a conversation
     *
     * Creates a new empty conversation for the authenticated user and returns its ID.
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @bodyParam title string optional Conversation title (max 200 chars). Example: "Pyetje për orarin"
     *
     * @response 201 {"data": {"id": 1}, "message": "Biseda u krijua me sukses.", "status": 201}
     */
    public function storeConversation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:200',
        ]);

        $conversation = ChatConversation::create([
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role,
            'title' => $data['title'] ?? null,
            'started_at' => now(),
        ]);

        return $this->success(['id' => $conversation->id], 'Biseda u krijua me sukses.', 201);
    }

    /**
     * Get conversation history
     *
     * Returns the full message list for a conversation, oldest first. Each assistant
     * message includes a `toolCalls` array (empty until tool calling ships in task 15).
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @urlParam id integer required The conversation ID. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "conversation": {"id": 1, "title": "Pyetje për orarin", "startedAt": "2026-06-07T09:10:00+00:00"},
     *     "messages": [
     *       {"id": 1, "role": "user", "content": "Kur e kam orën tjetër?", "tokenCount": 5, "createdAt": "2026-06-07T09:10:01+00:00", "toolCalls": []},
     *       {"id": 2, "role": "assistant", "content": "[Dija (fake)] Echo: Kur e kam orën tjetër?", "tokenCount": 10, "createdAt": "2026-06-07T09:10:01+00:00", "toolCalls": []}
     *     ]
     *   },
     *   "message": "OK",
     *   "status": 200
     * }
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function showConversation(Request $request, int $id): JsonResponse
    {
        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $messages = $conversation->messages()
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'tokenCount' => $m->token_count,
                'createdAt' => $m->created_at->toIso8601String(),
                'toolCalls' => $m->toolCalls->map(fn ($t) => [
                    'toolName' => $t->tool_name,
                    'input' => $t->input_json,
                    'output' => $t->output_json,
                    'durationMs' => $t->duration_ms,
                    'status' => $t->status,
                ])->all(),
            ]);

        return $this->success([
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'startedAt' => $conversation->started_at->toIso8601String(),
            ],
            'messages' => $messages,
        ], 'OK');
    }

    /**
     * Delete a conversation
     *
     * Deletes the conversation and cascades to all its messages and tool-call logs.
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @urlParam id integer required The conversation ID. Example: 1
     *
     * @response 200 {"data": null, "message": "Biseda u fshi me sukses.", "status": 200}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
     */
    public function destroyConversation(Request $request, int $id): JsonResponse
    {
        $conversation = ChatConversation::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $conversation->delete();

        return $this->success(null, 'Biseda u fshi me sukses.');
    }

    /**
     * Send a message (SSE stream)
     *
     * Posts a user message and streams the assistant reply back as Server-Sent Events
     * (`Content-Type: text/event-stream`) — this endpoint does NOT return JSON. Each
     * event is a `data:` line with one of these payloads:
     *
     *     data: {"type":"token","content":"..."}   ← one per streamed token
     *     data: {"type":"done"}                      ← stream finished successfully
     *     data: {"type":"error","message":"..."}    ← quota exceeded or failure
     *
     * After the stream closes, the user + assistant messages are persisted and the
     * caller's daily token usage is incremented. In Phase 1 the assistant is the echo
     * provider; the real LLM arrives in task 14.
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @urlParam id integer required The conversation ID. Example: 1
     *
     * @bodyParam content string required The user's message (max 4000 chars). Example: Kur e kam orën tjetër?
     *
     * @response 200 data: {"type":"token","content":"[Dija "}
     *
     * data: {"type":"done"}
     * @response 404 {"data": null, "message": "Not Found", "status": 404}
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
                if (ob_get_level()) {
                    ob_end_clean();
                }
                $this->sseEvent(['type' => 'error', 'message' => 'Keni arritur limitin ditor. Provoni nesër.']);
            }, 200, $this->sseHeaders());
        }

        return response()->stream(function () use ($conversation, $request, $data) {
            // Drop any active output buffer once, so events reach the client
            // immediately and sseEvent() never calls ob_flush() without a buffer.
            // Mirrors NotificationStreamController::stream().
            if (ob_get_level()) {
                ob_end_clean();
            }

            // A provider/stream failure mid-flight would otherwise break the SSE
            // connection with no signal. Emit a clean error event instead — the
            // frontend already handles {"type":"error"} (plan §4).
            try {
                $this->service->sendMessage(
                    conversation: $conversation,
                    user: $request->user(),
                    userContent: $data['content'],
                    onToken: fn (string $token) => $this->sseEvent(['type' => 'token', 'content' => $token]),
                );

                $this->sseEvent(['type' => 'done']);
            } catch (\Throwable $e) {
                report($e); // log for debugging
                $this->sseEvent(['type' => 'error', 'message' => 'Dija ndeshi një problem. Provo përsëri.']);
            }
        }, 200, $this->sseHeaders());
    }

    /**
     * Get today's usage
     *
     * Returns the authenticated user's token usage for the current day and the
     * configured daily limit (used for quota enforcement).
     *
     * @group Dija Chat
     *
     * @authenticated
     *
     * @response 200 {"data": {"tokensIn": 120, "tokensOut": 340, "messages": 6, "dailyLimit": 50000}, "message": "OK", "status": 200}
     */
    public function usage(Request $request): JsonResponse
    {
        $limit = config('services.anthropic.daily_token_limit', 50_000);

        $usage = ChatUsageDaily::where('user_id', $request->user()->id)
            ->where('day', now()->toDateString())
            ->first();

        if (! $usage) {
            return $this->success([
                'tokensIn' => 0,
                'tokensOut' => 0,
                'messages' => 0,
                'dailyLimit' => $limit,
            ], 'OK');
        }

        return $this->success([
            'tokensIn' => $usage->tokens_in,
            'tokensOut' => $usage->tokens_out,
            'messages' => $usage->messages,
            'dailyLimit' => $limit,
        ], 'OK');
    }

    /**
     * @return array<string, string>
     */
    private function sseHeaders(): array
    {
        return [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sseEvent(array $payload): void
    {
        echo 'data: ' . json_encode($payload) . "\n\n";
        // Only ob_flush() if a buffer is actually open — the stream callback calls
        // ob_end_clean() up front, so normally there is none. flush() pushes to the client.
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
}
