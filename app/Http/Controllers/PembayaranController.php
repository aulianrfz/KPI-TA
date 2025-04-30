<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peserta;
use App\Models\SubKategori;
use App\Models\Institusi;

use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index()
    {
        $peserta = Peserta::with('subKategori')
            ->where('user_id', Auth::id())
            ->get();

        return view('pembayaran.index', compact('peserta'));
    }

    public function bayar($id)
    {
        $peserta = Peserta::findOrFail($id);
        $subkategori = SubKategori::findOrFail($id);
        $institusi = Institusi::findOrFail($id);
        return view('pembayaran.detail', compact('peserta', 'subkategori', 'institusi'));
    }
}
