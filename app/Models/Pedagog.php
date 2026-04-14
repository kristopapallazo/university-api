<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pedagog extends Model
{
    protected $table = 'PEDAGOG';

    protected $primaryKey = 'PED_ID';

    public $timestamps = false;

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

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }
}
