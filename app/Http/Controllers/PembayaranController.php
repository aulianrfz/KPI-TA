<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peserta;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index()
    {
        // Ambil semua peserta milik user yang login
        $peserta = Peserta::with('subKategori')
            ->where('user_id', Auth::id())
            ->get();

        return view('pembayaran.index', compact('peserta'));
    }

    public function detail($id)
    {
        $peserta = Peserta::with('subKategori')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return view('pembayaran.detail', compact('peserta'));
    }
}
