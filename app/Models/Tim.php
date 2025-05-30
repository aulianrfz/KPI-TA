<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tim extends Model
{
    use HasFactory;

    protected $table = 'tim';

    protected $fillable = [
        'nama_tim',
    ];

    public function peserta()
    {
        return $this->belongsToMany(Peserta::class, 'bergabung', 'tim_id', 'peserta_id')
                    ->withPivot('posisi');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($tim) {            $tim->peserta()->detach();
        });
    }
}
