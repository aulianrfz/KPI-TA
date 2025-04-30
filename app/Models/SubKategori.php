<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubKategori extends Model
{
    use HasFactory;

    protected $table = 'sub_kategori';

    protected $fillable = [
        'kategori_id',
        'name_lomba',
        'jenis_lomba',
        'jurusan',
        'maks_peserta',
        'jenis_pelaksanaan',
        'deskripsi',
        'duration',
        'biaya_pendaftaran',
        'url_tor',
        'foto_kompetisi',
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriLomba::class, 'kategori_id');
    }
}
