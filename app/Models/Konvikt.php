<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Konvikt extends Model
{
    protected $table = 'KONVIKT';

    protected $primaryKey = 'KONV_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'KONV_EMER',
        'KONV_ADRESE',
        'KONV_KAPACITET',
    ];

    public function dhomat(): HasMany
    {
        return $this->hasMany(Dhome::class, 'KONV_ID', 'KONV_ID');
    }
}
