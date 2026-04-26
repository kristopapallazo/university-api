# 12 — Dija: Chat Tables Migration

> **Backlog ref:** Chatbot Phase 1 — DB layer
> **Owner:** Kristo only — this is a DB task (migrations + models). Do not assign to the BE team.
> **Priority:** P1 — blocks task 13 (BE skeleton)
> **Effort:** ~1.5h
> **Branch:** `kristo/chat-db`
> **After this task:** task 13 can be picked up

---

## Goal

Create the five `chat_*` tables that back the Dija chatbot, plus their Eloquent models. No existing table is altered. No routes, no controllers — pure DB layer.

Full spec in `docs/chatbot-plan.md §7`. This task is the concrete implementation of that section.

---

## Workflow

1. `git checkout main && git pull`
2. `git checkout -b kristo/chat-db`
3. Create migration, then models
4. Run `php artisan migrate` locally to verify
5. Run `make ci` before pushing
6. Open PR against `main`, request self-review

---

## Step 1 — Migration

```bash
php artisan make:migration create_chat_tables
```

Open the generated file and replace the `up()` / `down()` bodies with:

```php
public function up(): void
{
    // ── chat_conversations ────────────────────────────────────────
    Schema::create('chat_conversations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->string('user_role', 30);
        $table->string('title', 200)->nullable();
        $table->timestamp('started_at')->useCurrent();
        $table->timestamp('last_msg_at')->nullable();
        $table->timestamps();
    });

    // ── chat_messages ─────────────────────────────────────────────
    Schema::create('chat_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conversation_id')
            ->constrained('chat_conversations')
            ->cascadeOnDelete();
        $table->string('role', 20);          // 'user' | 'assistant' | 'tool'
        $table->text('content');
        $table->unsignedInteger('token_count')->nullable();
        $table->timestamp('created_at')->useCurrent();
    });

    // ── chat_tool_calls ───────────────────────────────────────────
    Schema::create('chat_tool_calls', function (Blueprint $table) {
        $table->id();
        $table->foreignId('message_id')
            ->constrained('chat_messages')
            ->cascadeOnDelete();
        $table->string('tool_name', 100);
        $table->json('input_json');
        $table->json('output_json')->nullable();
        $table->unsignedInteger('duration_ms')->nullable();
        $table->string('status', 20)->default('success'); // 'success' | 'error' | 'denied'
        $table->timestamp('created_at')->useCurrent();
    });

    // ── chat_documents ────────────────────────────────────────────
    // Static UAMD knowledge for RAG — populated by `php artisan chat:reindex`
    Schema::create('chat_documents', function (Blueprint $table) {
        $table->id();
        $table->string('source', 500);       // relative path to the .md file
        $table->unsignedSmallInteger('chunk_ix');  // chunk index within the file
        $table->text('content');
        $table->json('embedding')->nullable(); // Voyage AI vector (512 floats)
        $table->unique(['source', 'chunk_ix']);
    });

    // ── chat_usage_daily ──────────────────────────────────────────
    Schema::create('chat_usage_daily', function (Blueprint $table) {
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->date('day');
        $table->unsignedInteger('tokens_in')->default(0);
        $table->unsignedInteger('tokens_out')->default(0);
        $table->unsignedSmallInteger('messages')->default(0);
        $table->primary(['user_id', 'day']);
    });
}

public function down(): void
{
    Schema::dropIfExists('chat_usage_daily');
    Schema::dropIfExists('chat_documents');
    Schema::dropIfExists('chat_tool_calls');
    Schema::dropIfExists('chat_messages');
    Schema::dropIfExists('chat_conversations');
}
```

---

## Step 2 — Eloquent models

Create all five files below. None have a factory — they're created programmatically by `ChatService`.

### `app/Models/ChatConversation.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = ['user_id', 'user_role', 'title', 'started_at', 'last_msg_at'];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'last_msg_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }
}
```

### `app/Models/ChatMessage.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatMessage extends Model
{
    public $timestamps = false;

    protected $fillable = ['conversation_id', 'role', 'content', 'token_count', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function toolCalls(): HasMany
    {
        return $this->hasMany(ChatToolCall::class, 'message_id');
    }
}
```

### `app/Models/ChatToolCall.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatToolCall extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'message_id', 'tool_name',
        'input_json', 'output_json',
        'duration_ms', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'input_json'  => 'array',
            'output_json' => 'array',
            'created_at'  => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }
}
```

### `app/Models/ChatDocument.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatDocument extends Model
{
    public $timestamps = false;

    protected $fillable = ['source', 'chunk_ix', 'content', 'embedding'];

    protected function casts(): array
    {
        return ['embedding' => 'array'];
    }
}
```

### `app/Models/ChatUsageDaily.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatUsageDaily extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['user_id', 'day', 'tokens_in', 'tokens_out', 'messages'];

    protected function casts(): array
    {
        return ['day' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## Acceptance criteria

- [ ] `php artisan migrate` runs clean on a fresh DB
- [ ] `php artisan migrate:rollback` drops all five tables in reverse order without errors
- [ ] `php artisan migrate:fresh --seed` still runs (existing seeders unaffected)
- [ ] `make ci` passes
