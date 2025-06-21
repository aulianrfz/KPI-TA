<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Bergabung extends Pivot
{
    protected $table = 'peserta_tim';

    protected $fillable = [
        'peserta_id',
        'tim_id',
        'posisi',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class, 'peserta_id');
    }

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'tim_id');
    }
}
