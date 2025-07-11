<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JawabanKuisioner extends Model
{

    protected $table = 'jawaban_kuisioners';

    protected $fillable = ['peserta_id', 'kuisioner_id', 'jawaban'];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function kuisioner()
    {
        return $this->belongsTo(Kuisioner::class, 'kuisioner_id');
    }
}
