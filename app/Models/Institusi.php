<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Institusi extends Model
{
    protected $table = 'institusi';

    protected $fillable = [
        'nama_institusi',
        'alamat',
    ];
    public function peserta()
    {
        return $this->hasMany(Peserta::class);
    }
}
