<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudim extends Model
{
    protected $table = 'PROGRAM_STUDIM';

    protected $primaryKey = 'PROG_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'PROG_EM',
        'PROG_NIV',
        'PROG_KRD',
        'DEP_ID',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DEP_ID', 'DEP_ID');
    }

    public function kurrikulat(): HasMany
    {
        return $this->hasMany(Kurrikula::class, 'PROG_ID', 'PROG_ID');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'STUDENT_PROGRAM', 'PROG_ID', 'STU_ID')
            ->withPivot('STD_PRG_ID', 'STD_PRG_DTP', 'STD_PRG_DTM', 'STD_PRG_STATUS');
    }

    public function seksionet(): HasMany
    {
        return $this->hasMany(Seksion::class, 'PROG_ID', 'PROG_ID');
    }
}
