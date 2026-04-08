<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $table = 'DEPARTAMENT';

    protected $primaryKey = 'DEP_ID';

    public $timestamps = false;

    protected $fillable = [
        'DEP_EM',
        'FAK_ID',
        'PED_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'FAK_ID', 'FAK_ID');
    }
}
