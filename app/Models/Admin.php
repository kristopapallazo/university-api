<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'ADMIN';

    protected $primaryKey = 'ADM_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'ADM_EM',
        'ADM_MB',
        'ADM_EMAIL',
        'ADM_POZICION',
    ];
}
