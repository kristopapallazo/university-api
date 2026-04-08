<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $table = 'FAKULTET';

    protected $primaryKey = 'FAK_ID';

    public $timestamps = false;

    protected $fillable = [
        'FAK_EM',
        'PED_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'FAK_ID', 'FAK_ID');
    }
}
