<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agenda extends Model
{
    use HasFactory;
    protected $table = 'agenda';
    protected $fillable = [
        'jadwal_id',
        'mata_lomba_id',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'venue_id',
        'peserta_id',
        'juri_id',
        'tim_id',
    ];

    public function mataLomba()
    {
        return $this->belongsTo(MataLomba::class, 'mata_lomba_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function juri()
    {
        return $this->belongsTo(Juri::class, 'juri_id');
    }

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'tim_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

}
