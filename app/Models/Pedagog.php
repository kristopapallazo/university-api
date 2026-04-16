<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedagog extends Model
{
    use HasFactory;

    protected $table = 'PEDAGOG';

    protected $primaryKey = 'PED_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'PED_EM',
        'PED_MB',
        'PED_GJINI',
        'PED_TITULLI',
        'PED_EMAIL',
        'PED_TEL',
        'PED_DTL',
        'PED_DT_PUNESIM',
        'DEP_ID',
    ];

    protected function casts(): array
    {
        return [
            'PED_DTL' => 'date',
            'PED_DT_PUNESIM' => 'date',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }

    public function seksionet(): HasMany
    {
        return $this->hasMany(Seksion::class, 'PED_ID', 'PED_ID');
    }

    public function zyre(): HasOne
    {
        return $this->hasOne(Zyre::class, 'PED_ID', 'PED_ID');
    }
}
