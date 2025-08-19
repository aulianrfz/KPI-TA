<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    protected $table = 'event';

    protected $fillable = [
        'nama_event',
        'penyelenggara',
        'deskripsi',
        'tanggal',
        'tanggal_akhir',
        'foto',
        'biaya'
    ];

    public function kategori()
    {
        return $this->hasMany(KategoriLomba::class);
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'event_id');
    }

    public function supporterPendaftar()
    {
        return $this->hasMany(PendaftarSupporter::class, 'event_id');
    }

    public function sertifikatTemplate()
    {
        return $this->hasOne(SertifikatTemplate::class);
    }

    public function rekeningPembayaran()
    {
        return $this->hasOne(RekeningPembayaran::class, 'event_id');
    }
}
