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
    ];

    public function kategori()
    {
        return $this->hasMany(KategoriLomba::class);
    }
}
