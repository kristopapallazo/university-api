<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $FAT_ID
 * @property string $FAT_DAT_LESHIM
 * @property float $FAT_SHUMA
 * @property string $FAT_STATUSI
 * @property string|null $FAT_PERSHKRIM
 * @property int $STU_ID
 * @property-read Student $student
 */
class Fature extends Model
{
    use HasFactory;

    protected $table = 'FATURE';

    protected $primaryKey = 'FAT_ID';

    public $timestamps = false;

    protected $fillable = [
        'FAT_DAT_LESHIM',
        'FAT_SHUMA',
        'FAT_STATUSI',
        'FAT_PERSHKRIM',
        'STU_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'STU_ID', 'STU_ID');
    }
}
