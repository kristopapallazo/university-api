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

    /** List the authenticated user's conversations. */
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

    /** Start a new conversation. */
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

    /** Get full message history for a conversation. */
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
