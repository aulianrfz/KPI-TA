<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllPeserta extends Model
{
    //
    use HasFactory;

    protected $table = 'all_peserta'; // nama tabel
    protected $fillable = ['nama_ketua', 'nama_anggota', 'nomor_telepon', 'nama_institusi', 'keterangan'];
}
