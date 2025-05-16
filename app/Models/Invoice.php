<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoice';

    protected $fillable = [
        'total_tagihan',
        'jabatan',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Membayar::class);
    }
}
