<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $PROV_ID
 * @property string $TIP_EMER
 * @property string $DAT_PROVIM
 * @property int $SEK_ID
 * @property-read Seksion $seksion
 */
class Provim extends Model
{
    use HasFactory;

    protected $table = 'PROVIM';

    protected $primaryKey = 'PROV_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'TIP_EMER',
        'DAT_PROVIM',
        'SEK_ID',
    ];

    protected function casts(): array
    {
        return [
            'DAT_PROVIM' => 'date',
        ];
    }

    public function seksion(): BelongsTo
    {
        return $this->belongsTo(Seksion::class, 'SEK_ID', 'SEK_ID');
    }

    public function notat(): HasMany
    {
        return $this->hasMany(Nota::class, 'PROV_ID', 'PROV_ID');
    }
}
