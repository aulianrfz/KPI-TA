<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'pendaftar';

    protected $fillable = [
        'sub_kategori_id',
        'peserta_id',
        'url_qrCode',
        'status',
    ];

    public function subKategori()
    {
        return $this->belongsTo(SubKategori::class, 'sub_kategori_id');
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

}
