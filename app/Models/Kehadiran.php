<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kehadiran extends Model
{
    use HasFactory;
    protected $table = 'kehadiran';
    protected $fillable = [
        'pendaftar_id',
        'tanggal',
        'status'
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'pendaftar_id');
    }

}
