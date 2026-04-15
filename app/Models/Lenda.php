<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lenda extends Model
{
    protected $table = 'LENDA';

    protected $primaryKey = 'LEND_ID';

    public $timestamps = false;

    protected $fillable = [
        'LEND_EMER',
        'LEND_KOD',
        'DEP_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }
}
