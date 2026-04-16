<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Laborator extends Model
{
    protected $table = 'LABORATOR';

    protected $primaryKey = 'SALLE_ID';

    public $incrementing = false;

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'SALLE_ID',
        'LAB_PC_NR',
        'LAB_PAJISJE',
    ];

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(Auditor::class, 'SALLE_ID', 'SALL_ID');
    }
}
