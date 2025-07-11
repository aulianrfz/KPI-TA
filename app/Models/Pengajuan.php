<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengajuan extends Model
{
    use HasFactory;

    protected $table = 'pengajuans';

    protected $fillable = [
        'user_id',
        'jenis',
        'deskripsi',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
