<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semestr extends Model
{
    protected $table = 'SEMESTR';

    protected $primaryKey = 'SEM_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'SEM_NR',
        'SEM_DAT_FILLIMI',
        'SEM_DAT_MBARIMI',
        'VIT_ID',
    ];

    protected function casts(): array
    {
        return [
            'SEM_DAT_FILLIMI' => 'date',
            'SEM_DAT_MBARIMI' => 'date',
        ];
    }

    public function vitAkademik(): BelongsTo
    {
        return $this->belongsTo(VitAkademik::class, 'VIT_ID', 'VIT_ID');
    }

    public function seksionet(): HasMany
    {
        return $this->hasMany(Seksion::class, 'SEM_ID', 'SEM_ID');
    }
}
