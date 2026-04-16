<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $table = 'FAKULTET';

    protected $primaryKey = 'FAK_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'FAK_EM',
        'PED_ID',
    ];

    public function dean(): BelongsTo
    {
        return $this->belongsTo(Pedagog::class, 'PED_ID', 'PED_ID');
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'FAK_ID', 'FAK_ID');
    }
}
