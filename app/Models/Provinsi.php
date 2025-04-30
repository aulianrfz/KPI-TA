<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provinsi extends Model
{
    protected $table = 'provinsi';

    public function peserta()
    {
        return $this->hasMany(Peserta::class);
    }
}
