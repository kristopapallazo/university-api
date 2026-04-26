<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $NJOF_ID
 * @property int $USER_ID
 * @property string $NJOF_TITULL
 * @property string $NJOF_TEKST
 * @property string $NJOF_TIPI
 * @property bool $NJOF_IS_READ
 * @property string|null $NJOF_READ_AT
 * @property int|null $SENT_BY_ADMIN_ID
 * @property-read User $user
 */
class Njoftim extends Model
{
    protected $table = 'NJOFTIM';

    protected $primaryKey = 'NJOF_ID';

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'USER_ID',
        'NJOF_TITULL',
        'NJOF_TEKST',
        'NJOF_TIPI',
        'NJOF_IS_READ',
        'NJOF_READ_AT',
        'SENT_BY_ADMIN_ID',
    ];

    protected function casts(): array
    {
        return [
            'NJOF_IS_READ' => 'boolean',
            'NJOF_READ_AT' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'USER_ID', 'id');
    }

    // Called automatically by Laravel after every Njoftim::create()
    protected static function booted(): void
    {
        static::created(function (Njoftim $njoftim) {
            // Write a short-lived flag so the user's SSE stream knows to wake up.
            // Key format: sse_notify_{userId} — each user has their own flag.
            Cache::put("sse_notify_{$njoftim->USER_ID}", true, now()->addMinutes(5));
        });
    }
}
