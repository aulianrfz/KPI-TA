<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kuisioner extends Model
{

    protected $table = 'kuisioner';

    protected $fillable = ['event_id', 'pertanyaan'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function jawaban()
    {
        return $this->hasMany(JawabanKuisioner::class);
    }
}
