<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Peserta extends Model
{
    use HasFactory;
    protected $table = 'peserta';

    protected $fillable = [
        'nama',
        'nim',
        'nama_tim',
        'alamat_institusi',
        'provinsi_id',
        'institusi_id',
        'jurusan_id',
        'sub_kategori_id',
        'user_id',
        'email',
        'hp',
        'ktm_path',
        'ttd_path',
        'is_leader',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function institusi()
    {
        return $this->belongsTo(Institusi::class);
    }


    public function subKategori()
    {
        return $this->belongsTo(SubKategori::class, 'sub_kategori_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
