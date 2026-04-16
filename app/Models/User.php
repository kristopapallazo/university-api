<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $password
 * @property string $role
 * @property string|null $provider
 * @property string|null $provider_id
 * @property string|null $avatar_url
 * @property-read Student|null $student
 * @property-read Pedagog|null $pedagog
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'provider',
        'provider_id',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'provider_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasOne<Student, $this>
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'STU_EMAIL', 'email');
    }

    /**
     * @return HasOne<Pedagog, $this>
     */
    public function pedagog(): HasOne
    {
        return $this->hasOne(Pedagog::class, 'PED_EMAIL', 'email');
    }
}
