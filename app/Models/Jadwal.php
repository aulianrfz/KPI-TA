<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory;
    protected $table = 'jadwal';
    protected $fillable = [
        'nama_jadwal',
        'tahun',
        'version',
        'status',
        'alasan_gagal',
        // 'event_id'
    ];

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'jadwal_id');
    }

    // public function event()
    // {
    //     return $this->belongsTo(Event::class, 'event_id');
    // }


}
