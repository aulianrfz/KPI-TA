<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'pendaftar';

    protected $fillable = [
        'mata_lomba_id',
        'peserta_id',
        'url_qrCode',
    ];

    public function mataLomba()
    {
        return $this->belongsTo(MataLomba::class, 'mata_lomba_id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function kehadiran()
    {
        return $this->hasOne(Kehadiran::class, 'pendaftar_id');
    }

    public function membayar()
    {
        return $this->hasOne(Membayar::class, 'peserta_id', 'peserta_id');
    }

}
