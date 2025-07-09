<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supporter extends Model
{
    use HasFactory;

    protected $table = 'supporter';

    protected $fillable = [
        'user_id',
        'nama',
        'email',
        'instansi',
        'no_hp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pendaftaran()
    {
        return $this->hasOne(PendaftarSupporter::class, 'supporter_id');
    }

}
