<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Regjistrim extends Model
{
    protected $table = 'REGJISTRIM';

    protected $primaryKey = 'REGJ_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'DAT_REGJ',
        'REGJ_STATUS',
        'PIK_1',
        'PIK_2',
        'PIK_3',
        'SEK_ID',
        'STU_ID',
    ];

    protected function casts(): array
    {
        return [
            'DAT_REGJ' => 'date',
            'PIK_1' => 'decimal:2',
            'PIK_2' => 'decimal:2',
            'PIK_3' => 'decimal:2',
            'PIK_TOTAL' => 'decimal:2',
        ];
    }

    public function seksion(): BelongsTo
    {
        return $this->belongsTo(Seksion::class, 'SEK_ID', 'SEK_ID');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'STU_ID', 'STU_ID');
    }
}
