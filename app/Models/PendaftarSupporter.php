<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendaftarSupporter extends Model
{
    use HasFactory;

    protected $table = 'pendaftar_supporter';

    protected $fillable = [
        'event_id',
        'supporter_id',
        'url_qrCode',
        'status_kehadiran',
        'tanggal_kehadiran',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function supporter()
    {
        return $this->belongsTo(Supporter::class);
    }
}
