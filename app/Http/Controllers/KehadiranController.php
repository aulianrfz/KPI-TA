<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Kehadiran;
use App\Models\Bergabung;
use App\Models\KategoriLomba;
use App\Models\Tim;
use Carbon\Carbon;

class KehadiranController extends Controller
{
 public function index(Request $request)
    {
        $search = $request->input('search');

        $pendaftar = Pendaftar::with(['peserta', 'mataLomba', 'kehadiran'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('peserta', function ($q) use ($search) {
                    $q->where('nama_peserta', 'like', "%$search%")
                      ->orWhere('institusi', 'like', "%$search%");
                });
            })
            ->paginate(10);

        $totalPeserta = Pendaftar::count();
        $pesertaOnsite = Kehadiran::count();
        $belumDaftarUlang = $totalPeserta - $pesertaOnsite;

        return view('admin.kehadiran.index', compact('pendaftar', 'totalPeserta', 'pesertaOnsite', 'belumDaftarUlang'));
    }

    public function showQR($id)
    {
        $pendaftar = Pendaftar::with('peserta', 'mataLomba')->findOrFail($id);
        return view('admin.kehadiran.qr', compact('pendaftar'));
    }

    public function edit($id)
    {
        $pendaftar = Pendaftar::with('peserta', 'mataLomba', 'kehadiran')->findOrFail($id);
        $kehadiran = Kehadiran::all();
        return view('admin.kehadiran.edit', compact('pendaftar', 'kehadiran'));
    }

    public function update(Request $request, $id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        $pendaftar->update($request->only(['kategori', 'mata_lomba_id']));

        if ($request->filled('status')) {
            $kehadiran = new Kehadiran();
            $kehadiran->pendaftar_id = $pendaftar->id;
            $kehadiran->tanggal = now();
            $kehadiran->status = 'Hadir';
            $kehadiran->save();
        }

        return redirect()->route('kehadiran.index')->with('success', 'Data kehadiran berhasil diperbarui.');
    }

}
