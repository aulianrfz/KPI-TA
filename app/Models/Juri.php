<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juri extends Model
{
    use HasFactory;

    protected $table = 'juri'; 
    protected $fillable = ['nama', 'jabatan', 'mata_lomba_id'];

    public function mataLomba()
    {
        return $this->belongsTo(MataLomba::class);
    }
}