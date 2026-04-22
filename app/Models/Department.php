<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'DEPARTAMENT';

    protected $primaryKey = 'DEP_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'DEP_EM',
        'FAK_ID',
        'PED_ID',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'FAK_ID', 'FAK_ID');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(Pedagog::class, 'PED_ID', 'PED_ID');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(ProgramStudim::class, 'DEP_ID', 'DEP_ID');
    }

    public function lpidet(): HasMany
    {
        return $this->hasMany(Lenda::class, 'DEP_ID', 'DEP_ID');
    }

    public function pedagoget(): HasMany
    {
        return $this->hasMany(Pedagog::class, 'DEP_ID', 'DEP_ID');
    }
}
