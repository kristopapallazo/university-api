<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Zyre extends Model
{
    protected $table = 'ZYRE';

    protected $primaryKey = 'SALL_ID';

    public $incrementing = false;

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'SALL_ID',
        'ZYR_NR',
        'PED_ID',
    ];

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'SALL_ID', 'SALLE_ID');
    }

    public function pedagog(): BelongsTo
    {
        return $this->belongsTo(Pedagog::class, 'PED_ID', 'PED_ID');
    }
}
