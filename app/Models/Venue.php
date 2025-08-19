<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;
    protected $table = 'venue';
    protected $fillable = [
        'name',
        'tanggal_tersedia',
        'waktu_mulai_tersedia',
        'waktu_berakhir_tersedia',
    ];

     public function mataLomba()
    {
        return $this->hasMany(MataLomba::class, 'venue_id');
    }
}