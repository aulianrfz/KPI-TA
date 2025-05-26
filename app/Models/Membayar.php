<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membayar extends Model
{
    use HasFactory;

    protected $table = 'membayar';

    protected $fillable = [
        'invoice_id',
        'peserta_id',
        'bukti_pembayaran',
        'status',
        'waktu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class);
    }

    public function mataLomba()
    {
        return $this->belongsTo(MataLomba::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

}
