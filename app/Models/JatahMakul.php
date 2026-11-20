<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JatahMakul extends Model
{
    protected $table = 'tb_jatah_makul';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nim',
        'tahun_ajaran',
        'semester',
        'kode_makul',
        'nama_makul',
        'nipy',
        'nama_dosen',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'nim', 'nim');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'nipy', 'nipy');
    }
}
