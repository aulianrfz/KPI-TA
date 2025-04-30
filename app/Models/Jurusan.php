<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jurusan extends Model
{
    protected $table = 'jurusan';

    public function peserta()
    {
        return $this->hasMany(Peserta::class);
    }
}
