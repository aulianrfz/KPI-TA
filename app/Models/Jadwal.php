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
        'kategori_lomba',
        'waktu_mulai',
        'waktu_selesai',
        'venue',
        'peserta',
        'version'
    ];
}
