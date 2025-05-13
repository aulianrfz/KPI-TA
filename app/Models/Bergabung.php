<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Bergabung extends Pivot
{
    protected $table = 'bergabung';

    protected $fillable = [
        'peserta_id',
        'posisi',
    ];

    public function peserta()
    {
        return $this->belongsToMany(Peserta::class, 'bergabung', 'bergabung_id', 'peserta_id');
    }

}
