<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

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

    public function lenda(): BelongsTo
    {
        return $this->belongsTo(Lenda::class, 'LEND_ID', 'LEND_ID');
    }

    public function pedagog(): BelongsTo
    {
        return $this->belongsTo(Pedagog::class, 'PED_ID', 'PED_ID');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramStudim::class, 'PROG_ID', 'PROG_ID');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semestr::class, 'SEM_ID', 'SEM_ID');
    }

    public function sala(): BelongsTo
    {
        return $this->belongsTo(Auditor::class, 'SALL_ID', 'SALL_ID');
    }

    public function regjistrimet(): HasMany
    {
        return $this->hasMany(Regjistrim::class, 'SEK_ID', 'SEK_ID');
    }

    public function provimet(): HasMany
    {
        return $this->hasMany(Provim::class, 'SEK_ID', 'SEK_ID');
    }
}
