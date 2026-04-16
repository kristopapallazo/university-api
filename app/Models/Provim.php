<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $PROV_ID
 * @property string $TIP_EMER
 * @property string $DAT_PROVIM
 * @property int $SEK_ID
 * @property-read Seksion $seksion
 */
class Provim extends Model
{
    protected $table = 'PROVIM';

    protected $primaryKey = 'PROV_ID';

    public $timestamps = false;

    protected $fillable = [
        'TIP_EMER',
        'DAT_PROVIM',
        'SEK_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function seksion(): BelongsTo
    {
        return $this->belongsTo(Seksion::class, 'SEK_ID', 'SEK_ID');
    }
}
