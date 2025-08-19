<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RekeningPembayaran extends Model
{
    use HasFactory;

    protected $table = 'rekening_pembayaran';

    protected $fillable = [
        'event_id',
        'nama_bank',
        'no_rekening',
        'nama_rekening'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
