<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quisioner extends Model
{
    protected $table = 'tb_quisioner';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'judul_quisioner',
        'tahun_ajaran',
        'semester',
        'type',
        'hapus',
    ];

    protected function casts(): array
    {
        return [
            'hapus' => 'string',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('hapus', '0');
    }

    public function pertanyaan(): HasMany
    {
        return $this->hasMany(Pertanyaan::class, 'id_quisioner', 'id');
    }

    public function ketuntasan(): HasMany
    {
        return $this->hasMany(Ketuntasan::class, 'id_quisioner', 'id');
    }
}
