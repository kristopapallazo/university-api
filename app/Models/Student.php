<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $STU_ID
 * @property string $STU_EM
 * @property string $STU_MB
 * @property string|null $STU_ATESI
 * @property string $STU_GJINI
 * @property string $STU_DTL
 * @property string $STU_NR_MATRIKULL
 * @property string $STU_EMAIL
 * @property string|null $STU_TEL
 * @property string $STU_DAT_REGJISTRIM
 * @property string $STU_STATUS
 * @property int|null $DHOM_ID
 */
class Student extends Model
{
    use HasFactory;

    protected $table = 'STUDENT';

    protected $primaryKey = 'STU_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'STU_EM',
        'STU_MB',
        'STU_ATESI',
        'STU_GJINI',
        'STU_DTL',
        'STU_NR_MATRIKULL',
        'STU_EMAIL',
        'STU_TEL',
        'STU_DAT_REGJISTRIM',
        'STU_STATUS',
        'DHOM_ID',
    ];

    protected function casts(): array
    {
        return [
            'STU_DTL' => 'date',
            'STU_DAT_REGJISTRIM' => 'date',
        ];
    }

    public function dhome(): BelongsTo
    {
        return $this->belongsTo(Dhome::class, 'DHOM_ID', 'DHOM_ID');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(ProgramStudim::class, 'STUDENT_PROGRAM', 'STU_ID', 'PROG_ID')
            ->withPivot('STD_PRG_ID', 'STD_PRG_DTP', 'STD_PRG_DTM', 'STD_PRG_STATUS');
    }

    public function regjistrimet(): HasMany
    {
        return $this->hasMany(Regjistrim::class, 'STU_ID', 'STU_ID');
    }

    public function notat(): HasMany
    {
        return $this->hasMany(Nota::class, 'STU_ID', 'STU_ID');
    }

    public function faturat(): HasMany
    {
        return $this->hasMany(Fature::class, 'STU_ID', 'STU_ID');
    }

    public function huazimet(): HasMany
    {
        return $this->hasMany(Huazim::class, 'STU_ID', 'STU_ID');
    }
}
