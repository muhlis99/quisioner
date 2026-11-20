<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tb_users';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'string',
        ];
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'nim', 'username');
    }

    public function isMahasiswa(): bool
    {
        if ($this->relationLoaded('mahasiswa')) {
            return $this->getRelation('mahasiswa') !== null;
        }

        return $this->mahasiswa()->exists();
    }

    public function isAdmin(): bool
    {
        return ! $this->isMahasiswa();
    }

    public function getRoleAttribute(): string
    {
        return $this->isMahasiswa() ? 'mahasiswa' : 'admin';
    }
}
