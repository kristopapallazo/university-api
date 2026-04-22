<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Libn extends Model
{
    protected $table = 'LIBN';

    protected $primaryKey = 'LIBN_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'LIBN_TITULLI',
        'LIBN_AUTORI',
        'LIBN_ISBN',
        'LIBN_VITI',
        'LIBN_STATUSI',
    ];

    public function huazimet(): HasMany
    {
        return $this->hasMany(Huazim::class, 'LIBN_ID', 'LIBN_ID');
    }
}
