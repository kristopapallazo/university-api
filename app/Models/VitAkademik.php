<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VitAkademik extends Model
{
    protected $table = 'VIT_AKADEMIK';

    protected $primaryKey = 'VIT_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'VIT_EMER',
        'DATE_FILLIMI',
        'DATE_MBARIMI',
        'AKTIV',
    ];

    protected function casts(): array
    {
        return [
            'DATE_FILLIMI' => 'date',
            'DATE_MBARIMI' => 'date',
            'AKTIV' => 'boolean',
        ];
    }

    public function semestrat(): HasMany
    {
        return $this->hasMany(Semestr::class, 'VIT_ID', 'VIT_ID');
    }
}
