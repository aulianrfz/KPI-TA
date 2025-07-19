<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriLomba extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $fillable = [
        'event_id',
        'nama_kategori',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
