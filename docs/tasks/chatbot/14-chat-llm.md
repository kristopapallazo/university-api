# 14 — Dija: Real LLM (AnthropicChatProvider)

> **Backlog ref:** Chatbot Phase 2 — LLM integration
> **Priority:** P1 — comes after task 13 (BE skeleton) is merged
> **Effort:** ~3h
> **Stack:** Laravel 11, Anthropic Messages API via plain `Http::` / Guzzle (no SDK), SSE
> **Branch:** `<yourname>/chat-llm` (example: `ornela/chat-llm`)
> **Before you start:** task 13 must be merged (the echo bot works end-to-end). Read `docs/chatbot-plan.md` §3.3, §3.5, §6.1, §11.2. You also need a **real `ANTHROPIC_API_KEY`** in your local `.env` (the placeholder won't make calls).

---

## Goal

Replace `FakeChatProvider` with a real `AnthropicChatProvider` that streams Claude's reply token-by-token from the Anthropic Messages API. When this task is done:

- Sending a message to `/chat/conversations/{id}/messages` streams a **real Claude reply** over the existing SSE pipeline.
- Token counts come from the API's `usage` and feed the existing `chat_usage_daily` quota.
- The system prompt gives Dija its identity, the user's name/role, and a language instruction (reply in the user's language).

**Do NOT** add tools or RAG — that is task 15. `$tools` stays empty and `$onToolCall` is never called this phase. The architecture (interface, service, controller, SSE) is already in place from task 13; this task only adds one new `ChatProvider` implementation and flips one binding.

---

## Why no SDK

Per the plan (§11.2) we call the Anthropic API with Laravel's built-in `Http::` (Guzzle) — ~150 LOC, no new Composer dependency. One `POST https://api.anthropic.com/v1/messages` with `"stream": true`, then we parse the SSE response body ourselves. This keeps the dependency surface small and is more than enough for a single streaming endpoint.

---

## Workflow

1. `git checkout main && git pull` (after task 13 is merged)
2. `git checkout -b <yourname>/chat-llm`
3. Build: system-prompt builder → `AnthropicChatProvider` → swap binding
4. `make fix` before each commit, `make ci` before pushing
5. Smoke-test against the real API with a short message
6. Open PR against `main`, request review from `kristopapallazo`

---

## Step 0 — Confirm config & key

Task 13 already added these to `config/services.php` and `.env`:

```php
'anthropic' => [
    'key'               => env('ANTHROPIC_API_KEY'),
    'model'             => env('CHATBOT_MODEL', 'claude-haiku-4-5-20251001'),
    'daily_token_limit' => (int) env('CHATBOT_DAILY_TOKEN_LIMIT', 50000),
],
```

Put a **real** key in your local `.env`:

```
ANTHROPIC_API_KEY=sk-ant-...   # your real key, NOT the placeholder
```

> **Model:** we use **Haiku 4.5** (`claude-haiku-4-5-20251001`) for dev and most demos — cheapest, fast, fine for v1 (plan §12). For the final presentation you can switch `CHATBOT_MODEL` to `claude-sonnet-4-6` for higher quality. No code change — it's an env var.

---

## Step 1 — System-prompt builder

**File:** `app/Services/Chat/SystemPrompt.php`

Dija has "amnesia" (plan §9) — every request re-tells it who it is, who the user is, and what language to answer in. Keep this short and stable (a stable prefix is what makes prompt caching possible later — §3.5).

```php
<?php

namespace App\Services\Chat;

use App\Models\User;

class SystemPrompt
{
    public static function for(User $user): string
    {
        $name = $user->name;
        $role = $user->role; // 'student' | 'pedagog' | 'admin'

        return <<<PROMPT
        Ti je "Dija", asistenti virtual i Universitetit "Aleksandër Moisiu" Durrës (UAMD),
        i integruar në portalin eUAMD.

        Përdoruesi aktual: {$name} (roli: {$role}).

        Rregulla:
        - Përgjigju në GJUHËN e mesazhit të përdoruesit: shqip nëse shkruan shqip,
          anglisht nëse shkruan anglisht.
        - Ji i sjellshëm, i shkurtër dhe konkret. Përdor "ti" (jo "ju").
        - Mos shpik të dhëna. Nëse nuk e di diçka, thuaje hapur.
        - Në këtë version nuk ke ende akses te të dhënat personale (orari, notat, faturat);
          nëse të kërkohen, shpjego se kjo veçori po vjen së shpejti.
        PROMPT;
    }
}
```

> The last rule is temporary — it goes away in task 15 when tools give Dija real data access. For now it keeps Dija honest instead of hallucinating grades.

---

## Step 2 — AnthropicChatProvider

**File:** `app/Services/Chat/AnthropicChatProvider.php`

Implements the same `ChatProvider` interface as `FakeChatProvider` (task 13). It builds the request, opens a streaming HTTP call, parses the SSE events, calls `$onToken` for each text delta, and returns a `ChatResult` with the real token counts.

```php
<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Http;
use RuntimeException;

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
            'model'      => $this->model,
            'max_tokens' => 1024,
            // System prompt as a cacheable block. Caching only kicks in once the
            // prefix (system + tools) exceeds Haiku's 4096-token minimum, which
            // won't happen until tools/RAG land in task 15 — harmless to set now.
            'system' => [[
                'type'          => 'text',
                'text'          => $this->systemPrompt,
                'cache_control' => ['type' => 'ephemeral'],
            ]],
            'messages' => array_values($messages),
            'stream'   => true,
        ];

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type'      => 'application/json',
        ])->withOptions(['stream' => true])
          ->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Anthropic API error: HTTP '.$response->status());
        }

        return $this->consumeStream($response->toPsrResponse()->getBody(), $onToken);
    }

    /**
     * Read the SSE body chunk by chunk, dispatch text deltas, and collect token counts.
     *
     * @param  \Psr\Http\Message\StreamInterface  $body
     */
    private function consumeStream($body, callable $onToken): ChatResult
    {
        $buffer       = '';
        $fullText     = '';
        $inputTokens  = 0;
        $outputTokens = 0;

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            // SSE events are separated by a blank line ("\n\n").
            while (($sep = strpos($buffer, "\n\n")) !== false) {
                $rawEvent = substr($buffer, 0, $sep);
                $buffer   = substr($buffer, $sep + 2);

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
                                'Anthropic stream error: '.($json['error']['message'] ?? 'unknown')
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
```

### Notes

- **Token counts are authoritative now.** `FakeChatProvider` guessed with `str_word_count`; here they come from the API's `usage`, so the `chat_usage_daily` quota becomes real.
- **`messages` shape.** `ChatService` already passes `[['role' => 'user'|'assistant', 'content' => string], ...]`, which is exactly the Messages API shape — no transformation needed. The API requires the first message to be `user` and roles to alternate; our history is built that way.
- **`toPsrResponse()->getBody()`** gives the raw PSR-7 stream so we can read it incrementally. `Http::...->withOptions(['stream' => true])` is what stops Guzzle from buffering the whole body first.

---

## Step 3 — Bind the real provider

`ChatService` depends on `ChatProvider`. Task 13 bound it to `FakeChatProvider`. Now bind to `AnthropicChatProvider` **when a real key is present**, and keep the fake as a fallback so tests and keyless setups still work.

Open `app/Providers/AppServiceProvider.php`, replace the task-13 binding in `register()`:

```php
use App\Services\Chat\AnthropicChatProvider;
use App\Services\Chat\ChatProvider;
use App\Services\Chat\FakeChatProvider;

$this->app->bind(ChatProvider::class, function ($app) {
    $key = config('services.anthropic.key');

    // No real key (CI, fresh clone, tests) → echo bot. Real key → Claude.
    if (! $key || $key === 'sk-ant-placeholder') {
        return new FakeChatProvider();
    }

    // System prompt needs the authenticated user; resolve it per-request.
    $user = $app['request']->user();

    return new AnthropicChatProvider(
        apiKey: $key,
        model: config('services.anthropic.model'),
        systemPrompt: \App\Services\Chat\SystemPrompt::for($user),
    );
});
```

> **Why the key check:** `make ci` runs on machines without a real key, and the feature tests should keep using the deterministic echo bot. Gating on the key means the test suite stays fast and free, and a fresh clone still boots. Only a developer with a real key in `.env` hits the live API.

---

## Step 4 — Surface API errors over SSE

`ChatController::sendMessage` (task 13) wraps the stream in a closure. A thrown `RuntimeException` from the provider would break the stream mid-flight. Wrap the service call so a failure emits a clean `error` SSE event instead of a 500:

In `ChatController::sendMessage`, change the streaming closure body to:

```php
return response()->stream(function () use ($conversation, $request, $data) {
    if (ob_get_level()) {
        ob_end_clean();
    }

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
```

> The frontend already handles `{"type":"error"}` events (plan §4), so this degrades gracefully — the user sees a friendly Albanian message, not a broken stream.

---

## Manual smoke test

With a real key in `.env` and `make dev` running (login with a password account — students are OAuth-only; see task 13):

```bash
BASE=http://localhost:8000/api/v1
TOKEN=$(curl -s -X POST $BASE/auth/login -H "Content-Type: application/json" \
  -d '{"email":"test.admin@uamd.edu.al","password":"Testtest1!"}' | jq -r '.data.token')

CONV_ID=$(curl -s -X POST $BASE/chat/conversations -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" -d '{"title":"Test LLM"}' | jq -r '.data.id')

# Real Claude reply, streamed:
curl -N -X POST $BASE/chat/conversations/$CONV_ID/messages \
  -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" \
  -d '{"content":"Përshëndetje, kush je ti?"}'
# Expect: token events forming a real Albanian self-introduction, then {"type":"done"}

# Usage now reflects real API token counts:
curl -s $BASE/chat/usage -H "Authorization: Bearer $TOKEN" | jq '.data'
```

Also confirm the **fallback**: temporarily set `ANTHROPIC_API_KEY=sk-ant-placeholder`, re-run the message call, and verify you get the `[Dija (fake)] Echo: ...` reply again. Restore your real key after.

---

## Acceptance criteria

- [ ] With a real key, `POST /chat/conversations/{id}/messages` streams a genuine Claude reply (not the echo) over SSE, ending with `{"type":"done"}`
- [ ] Dija replies in the language of the user's message (Albanian → Albanian, English → English)
- [ ] `chat_usage_daily` increments using the **API's** token counts (not word-count guesses)
- [ ] With `ANTHROPIC_API_KEY=sk-ant-placeholder`, the app falls back to `FakeChatProvider` (echo)
- [ ] An API/stream failure emits `{"type":"error", ...}` and is logged — no 500, no broken stream
- [ ] No tools are sent and `$onToolCall` is never invoked (that's task 15)
- [ ] `make ci` passes (tests still use the fake provider, so they stay deterministic and offline)
