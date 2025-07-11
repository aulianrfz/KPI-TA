<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Peserta extends Model
{
    use HasFactory;
    protected $table = 'peserta';

    protected $fillable = [
        'nama_peserta',
        'nim',
        'provinsi',
        'institusi',
        'prodi',
        'user_id',
        'email',
        'no_hp',
        'url_ktm',
        'url_ttd',
        'jenis_peserta',
        'sertifikat_generated',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function institusi()
    {
        return $this->belongsTo(Institusi::class);
    }

    public function mataLomba()
    {
        return $this->belongsTo(MataLomba::class, 'mata_lomba_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tim()
    {
        return $this->belongsToMany(Tim::class, 'peserta_tim', 'peserta_id', 'tim_id')
                    ->withPivot('posisi');
    }

    public function bergabung()
    {
        return $this->hasOne(Bergabung::class,  'peserta_id');
    }

    public function pendaftar()
    {
        return $this->hasOne(Pendaftar::class, 'peserta_id');
    }

    public function membayar()
    {
        return $this->hasMany(Membayar::class, 'peserta_id');
    }

    public function jawabanKuisioner()
    {
        return $this->hasMany(JawabanKuisioner::class, 'peserta_id');
    }
}
