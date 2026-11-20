<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ketuntasan extends Model
{
    protected $table = 'tb_ketuntasan';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_quisioner',
        'tahun_ajaran',
        'semester',
        'niim',
        'status_insert_soal',
        'status_penyelesaian',
        'ketuntasan',
    ];

    protected function casts(): array
    {
        return [
            'status_insert_soal' => 'integer',
            'status_penyelesaian' => 'integer',
            'ketuntasan' => 'string',
        ];
    }

    public function quisioner(): BelongsTo
    {
        return $this->belongsTo(Quisioner::class, 'id_quisioner', 'id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'niim', 'nim');
    }
}
