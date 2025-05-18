<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;
use App\Models\Bergabung;
use App\Models\Pendaftar;
use Illuminate\Http\Request;

class MyEventController extends Controller
{
    public function index()
    {
        $pendaftarList = Pendaftar::with(['subKategori', 'peserta.bergabung'])
            ->whereHas('peserta', function ($query) {
                $query->where('user_id', Auth::id())
                      ->where(function ($q) {
                          $q->whereDoesntHave('bergabung')
                            ->orWhereHas('bergabung', function ($subQ) {
                                $subQ->where('posisi', 'ketua');
                            });
                      });
            })
            ->get();
    
        return view('user.my-event.list', compact('pendaftarList'));
    }
}
