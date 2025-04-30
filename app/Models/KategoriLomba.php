<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriLomba extends Model
{
    use HasFactory;

    protected $table = 'kategori_lomba';

    protected $fillable = [
        'name',
    ];
}
