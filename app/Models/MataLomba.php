<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MataLomba extends Model
{
    use HasFactory;

    protected $table = 'mata_lomba';

    protected $fillable = [
        'kategori_id',
        'venue_id',
        'nama_lomba',
        'jenis_lomba',
        'jurusan',
        'min_peserta',
        'maks_peserta',
        'maks_total_peserta',
        'jenis_pelaksanaan',
        'deskripsi',
        'durasi',
        'biaya_pendaftaran',
        'url_tor',
        'foto_kompetisi',
        'is_serentak'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriLomba::class, 'kategori_id');
    }

     public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }
}
