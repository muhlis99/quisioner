<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    protected $table = 'tb_dosen';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nipy',
        'nama',
    ];

    public function jatahMakul(): HasMany
    {
        return $this->hasMany(JatahMakul::class, 'nipy', 'nipy');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'nipy', 'nipy');
    }
}
