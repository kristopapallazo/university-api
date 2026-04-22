<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Salle extends Model
{
    protected $table = 'SALLE';

    protected $primaryKey = 'SALLE_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'SALLE_NR',
        'SALLE_KAPACITET',
        'SALLE_LLOJ',
    ];

    public function auditor(): HasOne
    {
        return $this->hasOne(Auditor::class, 'SALL_ID', 'SALLE_ID');
    }

    public function zyre(): HasOne
    {
        return $this->hasOne(Zyre::class, 'SALL_ID', 'SALLE_ID');
    }
}
