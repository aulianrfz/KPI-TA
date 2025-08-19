<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Pembimbing extends Model
{
    use HasFactory;

    protected $table = 'pembimbing';

    protected $fillable = [
        'user_id',
        'nama_lengkap',
        'nip',
        'instansi',
        'jabatan',
        'no_hp',
        'email',
        'url_surat_tugas',
        'url_visum',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pendaftaran()
    {
        return $this->hasMany(PendaftarPembimbing::class, 'pembimbing_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(PembayaranPembimbing::class, 'pembimbing_id');
    }

}
