<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lenda extends Model
{
    use HasFactory;

    protected $table = 'LENDA';

    protected $primaryKey = 'LEND_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'LEND_EMER',
        'LEND_KOD',
        'DEP_ID',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }

    public function seksionet(): HasMany
    {
        return $this->hasMany(Seksion::class, 'LEND_ID', 'LEND_ID');
    }

    public function kurrikulat(): HasMany
    {
        return $this->hasMany(Kurrikula::class, 'LEND_ID', 'LEND_ID');
    }
}
