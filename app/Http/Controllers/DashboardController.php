<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Peserta;

class DashboardController extends Controller
{
    public function index()
    {
        $pesertaList = Peserta::with(['subKategori', 'institusi'])
            ->where('user_id', Auth::id())
            ->get();

        return view('dashboard.list', compact('pesertaList'));
    }
}

