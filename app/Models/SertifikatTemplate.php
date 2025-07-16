<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SertifikatTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'nama_file',
        'posisi_x',
        'posisi_y',
        'font',
        'font_dompdf',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
