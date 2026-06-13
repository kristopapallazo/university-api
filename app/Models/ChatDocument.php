<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $source
 * @property int $chunk_ix
 * @property string $content
 * @property array|null $embedding
 */
class ChatDocument extends Model
{
    public $timestamps = false;

    protected $fillable = ['source', 'chunk_ix', 'content', 'embedding'];

    protected function casts(): array
    {
        return ['embedding' => 'array'];
    }
}
