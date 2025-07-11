<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPembimbing extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_pembimbing';

    protected $fillable = [
        'invoice_id',
        'pembimbing_id',
        'bukti_pembayaran',
        'status',
        'waktu',
    ];

    public function pembimbing()
    {
        return $this->belongsTo(Pembimbing::class, 'pembimbing_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
