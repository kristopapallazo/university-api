<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Auditor extends Model
{
    protected $table = 'AUDITOR';

    protected $primaryKey = 'SALL_ID';

    public $incrementing = false;

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'SALL_ID',
        'AUD_Y',
        'AUD_TIP',
    ];

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'SALL_ID', 'SALLE_ID');
    }

    public function laborator(): HasOne
    {
        return $this->hasOne(Laborator::class, 'SALLE_ID', 'SALL_ID');
    }

    public function seksionet(): HasMany
    {
        return $this->hasMany(Seksion::class, 'SALL_ID', 'SALL_ID');
    }
}
