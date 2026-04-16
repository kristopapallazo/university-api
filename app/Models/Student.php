<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $STU_ID
 * @property string $STU_EM
 * @property string $STU_MB
 * @property string|null $STU_ATESI
 * @property string|null $STU_GJINI
 * @property string|null $STU_DTL
 * @property string $STU_NR_MATRIKULL
 * @property string $STU_EMAIL
 * @property string|null $STU_TEL
 * @property string|null $STU_DAT_REGJISTRIM
 * @property string|null $STU_STATUS
 * @property int|null $DHOM_ID
 */
class Student extends Model
{
    use HasFactory;

    protected $table = 'STUDENT';

    protected $primaryKey = 'STU_ID';

    public $timestamps = false;

    protected $fillable = [
        'STU_EM',
        'STU_MB',
        'STU_ATESI',
        'STU_GJINI',
        'STU_DTL',
        'STU_NR_MATRIKULL',
        'STU_EMAIL',
        'STU_TEL',
        'STU_DAT_REGJISTRIM',
        'STU_STATUS',
        'DHOM_ID',
    ];

    const CREATED_AT = 'CREATED_AT';

    const UPDATED_AT = 'UPDATED_AT';
}
