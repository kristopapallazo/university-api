<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dhome extends Model
{
    protected $table = 'DHOME';

    protected $primaryKey = 'DHOM_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'DHOM_NR',
        'DHOM_KAPACITET',
        'KONV_ID',
    ];

    public function konvikt(): BelongsTo
    {
        return $this->belongsTo(Konvikt::class, 'KONV_ID', 'KONV_ID');
    }

    public function studentet(): HasMany
    {
        return $this->hasMany(Student::class, 'DHOM_ID', 'DHOM_ID');
    }
}
