<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $message_id
 * @property string $tool_name
 * @property array $input_json
 * @property array|null $output_json
 * @property int|null $duration_ms
 * @property string $status
 * @property Carbon $created_at
 * @property-read ChatMessage $message
 */
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
            'input_json' => 'array',
            'output_json' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ChatMessage, $this>
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }
}
