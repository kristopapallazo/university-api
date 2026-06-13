<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_usage_daily');
        Schema::dropIfExists('chat_documents');
        Schema::dropIfExists('chat_tool_calls');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
