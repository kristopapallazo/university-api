<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Huazim extends Model
{
    protected $table = 'HUAZIM';

    protected $primaryKey = 'HUAZ_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'HUAZ_DAT_MARRE',
        'HUAZ_DAT_KTHIM',
        'HUAZ_DAT_KTHYER',
        'STU_ID',
        'LIBN_ID',
    ];

    protected function casts(): array
    {
        return [
            'HUAZ_DAT_MARRE' => 'date',
            'HUAZ_DAT_KTHIM' => 'date',
            'HUAZ_DAT_KTHYER' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'STU_ID', 'STU_ID');
    }

    public function liber(): BelongsTo
    {
        return $this->belongsTo(Libn::class, 'LIBN_ID', 'LIBN_ID');
    }
}
