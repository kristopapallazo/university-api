<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $SEK_ID
 * @property string $DITA
 * @property string $ORE_FILLIMI
 * @property string $ORE_MBARIMI
 * @property int $LEND_ID
 * @property int $PED_ID
 * @property int $PROG_ID
 * @property int $SEM_ID
 * @property int $SALL_ID
 * @property-read Lenda $lenda
 * @property-read Pedagog $pedagog
 */
class Seksion extends Model
{
    use HasFactory;

    protected $table = 'SEKSION';

    protected $primaryKey = 'SEK_ID';

    public $timestamps = false;

    protected $fillable = [
        'DITA',
        'ORE_FILLIMI',
        'ORE_MBARIMI',
        'LEND_ID',
        'PED_ID',
        'PROG_ID',
        'SEM_ID',
        'SALL_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function lenda(): BelongsTo
    {
        return $this->belongsTo(Lenda::class, 'LEND_ID', 'LEND_ID');
    }

    public function pedagog(): BelongsTo
    {
        return $this->belongsTo(Pedagog::class, 'PED_ID', 'PED_ID');
    }
}
