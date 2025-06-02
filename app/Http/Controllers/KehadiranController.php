<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Kehadiran;
use App\Models\Event;
use App\Models\Bergabung;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KehadiranExport;
use App\Models\KategoriLomba;
use App\Models\MataLomba;
use App\Models\Tim;
use Carbon\Carbon;

class KehadiranController extends Controller
{

    public function event()
    {
        $events = Event::all();
        return view('admin.kehadiran.event', compact('events'));
    }

    public function kategori($eventId, Request $request)
    {
        $event = Event::with('kategori')->findOrFail($eventId);

        $categories = $event->kategori;

        if ($request->filled('search')) {
            $categories = $categories->filter(function ($kategori) use ($request) {
                return str_contains(strtolower($kategori->nama_kategori), strtolower($request->search));
            });
        }

        return view('admin.kehadiran.kategori', compact('event', 'categories'));
    }

    public function mataLomba($kategori_id, Request $request)
    {
        $query = MataLomba::where('kategori_id', $kategori_id);

        $events = $query->get();
        return view('admin.kehadiran.mataLomba', compact('events'));
    }


public function index(Request $request, $mataLombaId)
{
    $search = $request->input('search');
    $sort = $request->input('sort', 'desc');

    $pendaftar = Pendaftar::with(['peserta', 'mataLomba', 'kehadiran'])
        ->where('mata_lomba_id', $mataLombaId)
        ->whereNotNull('url_qrCode')
        ->where('url_qrCode', '!=', '0')
        ->when($search, function ($query) use ($search) {
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%$search%")
                    ->orWhere('institusi', 'like', "%$search%");
            });
        })
        ->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
        ->paginate(10);

    $pendaftar->appends([
        'search' => $search,
        'sort' => $sort,
    ]);

    $totalPeserta = Pendaftar::where('mata_lomba_id', $mataLombaId)
        ->whereNotNull('url_qrCode')
        ->where('url_qrCode', '!=', '0')
        ->count();

    $pesertaOnsite = Kehadiran::whereHas('pendaftar', function ($q) use ($mataLombaId) {
        $q->where('mata_lomba_id', $mataLombaId);
    })->where('status', 'Hadir')->count();

    $belumDaftarUlang = $totalPeserta - $pesertaOnsite;

    return view('admin.kehadiran.index', compact(
        'pendaftar',
        'totalPeserta',
        'pesertaOnsite',
        'belumDaftarUlang'
    ));
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

        if ($pendaftar->kehadiran && $pendaftar->kehadiran->status === 'Hadir') {
            return redirect()->route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id])
                ->with('warning', 'Peserta sudah melakukan kehadiran. Data tidak dapat diubah.');
        }

        $kehadiran = $pendaftar->kehadiran ?? new Kehadiran();
        $kehadiran->pendaftar_id = $pendaftar->id;
        $kehadiran->tanggal = now();
        $kehadiran->status = $request->input('status');
        $kehadiran->save();

        return redirect()->route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id])
            ->with('success', 'Data kehadiran berhasil diperbarui.');
    }


    public function exportExcel(Request $request)
    {
        return Excel::download(new KehadiranExport($request->search), 'kehadiran.xlsx');
    }

}
