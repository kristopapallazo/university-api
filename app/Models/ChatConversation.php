<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $user_role
 * @property string|null $title
 * @property Carbon $started_at
 * @property Carbon|null $last_msg_at
 * @property-read User $user
 * @property-read Collection<int, ChatMessage> $messages
 */
class ChatConversation extends Model
{
    protected $fillable = ['user_id', 'user_role', 'title', 'started_at', 'last_msg_at'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
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
