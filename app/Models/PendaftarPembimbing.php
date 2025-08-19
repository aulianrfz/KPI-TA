<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PendaftarPembimbing extends Model
{
    use HasFactory;

    protected $table = 'pendaftar_pembimbing';

    protected $fillable = [
        'event_id',
        'pembimbing_id',
        'url_qrCode',
        'status_kehadiran',
        'tanggal_kehadiran',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranPembimbing::class, 'pendaftar_pembimbing_id');
    }
}
