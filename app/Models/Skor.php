<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skor extends Model
{
    protected $table = 'tb_skor';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'skor',
        'keterangan',
        'huruf',
    ];

    public function hasil(): HasMany
    {
        return $this->hasMany(Hasil::class, 'skor', 'skor');
    }
}
