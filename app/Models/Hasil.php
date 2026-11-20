<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hasil extends Model
{
    protected $table = 'tb_hasil';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'tanggal',
        'type',
        'nipy',
        'nim',
        'id_pertanyaan',
        'isi_pertanyaan',
        'skor',
        'isi_jawaban',
        'kode_makul',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'skor' => 'integer',
        ];
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'nipy', 'nipy');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(Pertanyaan::class, 'id_pertanyaan', 'id');
    }

    public function detailSkor(): BelongsTo
    {
        return $this->belongsTo(Skor::class, 'skor', 'skor');
    }
}
