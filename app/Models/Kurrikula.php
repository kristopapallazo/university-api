<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kurrikula extends Model
{
    protected $table = 'KURRIKULA';

    protected $primaryKey = 'KURR_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'KURR_VIT',
        'KURR_NR_SEMESTER',
        'KURR_KREDIT',
        'KURR_I_DETYRUESHEM',
        'PROG_ID',
        'LEND_ID',
    ];

    protected function casts(): array
    {
        return [
            'KURR_I_DETYRUESHEM' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramStudim::class, 'PROG_ID', 'PROG_ID');
    }

    public function lenda(): BelongsTo
    {
        return $this->belongsTo(Lenda::class, 'LEND_ID', 'LEND_ID');
    }
}
