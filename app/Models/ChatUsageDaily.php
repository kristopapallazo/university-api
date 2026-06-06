<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Per-user daily token quota. Composite primary key (user_id, day), no auto-id.
 * Written via DB upsert/increment in ChatService; read via where()->first().
 *
 * @property int $user_id
 * @property Carbon $day
 * @property int $tokens_in
 * @property int $tokens_out
 * @property int $messages
 * @property-read User $user
 */
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
