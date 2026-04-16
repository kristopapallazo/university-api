<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgram extends Model
{
    protected $table = 'STUDENT_PROGRAM';

    protected $primaryKey = 'STD_PRG_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'STD_PRG_DTP',
        'STD_PRG_DTM',
        'STD_PRG_STATUS',
        'STU_ID',
        'PROG_ID',
    ];

    protected function casts(): array
    {
        return [
            'STD_PRG_DTP' => 'date',
            'STD_PRG_DTM' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'STU_ID', 'STU_ID');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramStudim::class, 'PROG_ID', 'PROG_ID');
    }
}
