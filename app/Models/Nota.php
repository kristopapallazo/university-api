<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $NOTA_ID
 * @property float $NOTA_VLERA
 * @property string $NOTA_DAT
 * @property int $STU_ID
 * @property int $PROV_ID
 * @property-read Provim $provim
 * @property-read Student $student
 */
class Nota extends Model
{
    use HasFactory;

    protected $table = 'NOTA';

    protected $primaryKey = 'NOTA_ID';

    public $timestamps = true;

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    protected $fillable = [
        'NOTA_VLERA',
        'NOTA_DAT',
        'STU_ID',
        'PROV_ID',
    ];

    protected function casts(): array
    {
        return [
            'NOTA_VLERA' => 'decimal:2',
            'NOTA_DAT' => 'date',
        ];
    }

    public function provim(): BelongsTo
    {
        return $this->belongsTo(Provim::class, 'PROV_ID', 'PROV_ID');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'STU_ID', 'STU_ID');
    }
}
