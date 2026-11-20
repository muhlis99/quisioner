<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pertanyaan extends Model
{
    protected $table = 'tb_pertanyaan';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'id_quisioner',
        'kategori',
        'isi_pertanyaan',
        'type',
    ];

    public function quisioner(): BelongsTo
    {
        return $this->belongsTo(Quisioner::class, 'id_quisioner', 'id');
    }

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'id_pertanyaan', 'id');
    }
}
