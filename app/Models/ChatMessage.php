<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $conversation_id
 * @property string $role
 * @property string $content
 * @property int|null $token_count
 * @property Carbon $created_at
 * @property-read ChatConversation $conversation
 * @property-read Collection<int, ChatToolCall> $toolCalls
 */
class ChatMessage extends Model
{
    public $timestamps = false;

    protected $fillable = ['conversation_id', 'role', 'content', 'token_count', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<ChatConversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * @return HasMany<ChatToolCall, $this>
     */
    public function toolCalls(): HasMany
    {
        return $this->hasMany(ChatToolCall::class, 'message_id');
    }
}
