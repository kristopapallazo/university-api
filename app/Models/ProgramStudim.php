<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramStudim extends Model
{
    protected $table = 'PROGRAM_STUDIM';

    protected $primaryKey = 'PROG_ID';

    public $timestamps = false;

    protected $fillable = [
        'PROG_EM',
        'PROG_NIV',
        'PROG_KRD',
        'DEP_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }
}
