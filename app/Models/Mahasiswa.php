<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mahasiswa extends Model
{
    protected $table = 'tb_mahasiswa';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nim',
        'nama',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'username', 'nim');
    }

    public function jatahMakul(): HasMany
    {
        return $this->hasMany(JatahMakul::class, 'nim', 'nim');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'nim', 'nim');
    }

    public function ketuntasan(): HasMany
    {
        return $this->hasMany(Ketuntasan::class, 'niim', 'nim');
    }
}
