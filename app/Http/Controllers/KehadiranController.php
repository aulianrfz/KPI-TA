<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Event;
use App\Models\KategoriLomba;
use App\Models\MataLomba;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KehadiranExport;
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
        session(['selected_event' => $eventId]);
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
        $events = MataLomba::where('kategori_id', $kategori_id)->get();
        return view('admin.kehadiran.mataLomba', compact('events'));
    }


    public function pilihJenisByEvent($eventId)
    {
        $event = Event::findOrFail($eventId);
        return view('admin.kehadiran.pilih_jenis', compact('event'));
    }

    public function kehadiranJenis(Request $request, $eventId, $jenis)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'desc');

        if ($jenis === 'supporter') {
            $pendaftar = \App\Models\PendaftarSupporter::with('supporter')
                ->where('event_id', $eventId)
                // ->whereNotNull('url_qrCode')
                // ->where('url_qrCode', '!=', '0')
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('supporter', function ($q) use ($search) {
                        $q->where('nama', 'like', "%$search%")
                            ->orWhere('instansi', 'like', "%$search%");
                    });
                })
                ->orderBy('created_at', $sort)
                ->paginate(10);

            return view('admin.kehadiran.index_supporter', compact('pendaftar', 'eventId'));
        }

        if ($jenis === 'pendamping') {
            $pendaftar = \App\Models\PendaftarPembimbing::with('pembimbing')
                ->where('event_id', $eventId)
                ->whereNotNull('url_qrCode')
                ->where('url_qrCode', '!=', '0')
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('pembimbing', function ($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%$search%")
                            ->orWhere('instansi', 'like', "%$search%");
                    });
                })
                ->orderBy('created_at', $sort)
                ->paginate(10);

            return view('admin.kehadiran.index_pembimbing', compact('pendaftar', 'eventId'));
        }

        abort(404);
    }


    // public function pilihJenis($mataLombaId)
    // {
    //     $mataLomba = MataLomba::with('kategori.event')->findOrFail($mataLombaId);
    //     return view('admin.kehadiran.pilih_jenis', compact('mataLomba'));
    // }

    // public function kehadiranJenis(Request $request, $mataLombaId, $jenis)
    // {
    //     if ($jenis === 'peserta') {
    //         return $this->index($request, $mataLombaId);
    //     }

    //     $search = $request->input('search');
    //     $sort = $request->input('sort', 'desc');

    //     if ($jenis === 'supporter') {
    //         $pendaftar = \App\Models\PendaftarSupporter::with('supporter')
    //             ->whereHas('supporter')
    //             ->whereNotNull('url_qrCode')
    //             ->where('url_qrCode', '!=', '0')
    //             ->when($search, function ($query) use ($search) {
    //                 $query->whereHas('supporter', function ($q) use ($search) {
    //                     $q->where('nama', 'like', "%$search%")
    //                     ->orWhere('instansi', 'like', "%$search%");
    //                 });
    //             })
    //             ->orderBy('created_at', $sort)
    //             ->paginate(10);

    //         return view('admin.kehadiran.index_supporter', compact('pendaftar', 'mataLombaId'));
    //     }

    //     if ($jenis === 'pendamping') {
    //         $pendaftar = \App\Models\PendaftarPembimbing::with('pembimbing')
    //             ->whereHas('pembimbing')
    //             ->whereNotNull('url_qrCode')
    //             ->where('url_qrCode', '!=', '0')
    //             ->when($search, function ($query) use ($search) {
    //                 $query->whereHas('pembimbing', function ($q) use ($search) {
    //                     $q->where('nama_lengkap', 'like', "%$search%")
    //                     ->orWhere('instansi', 'like', "%$search%");
    //                 });
    //             })
    //             ->orderBy('created_at', $sort)
    //             ->paginate(10);

    //         return view('admin.kehadiran.index_pembimbing', compact('pendaftar', 'mataLombaId'));
    //     }

    //     abort(404);
    // }

    public function index(Request $request, $mataLombaId)
    {
        session(['selected_event' => $mataLombaId]);
        $search = $request->input('search');
        $sort = $request->input('sort', 'desc');

        $pendaftar = Pendaftar::with(['peserta', 'mataLomba'])
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

        $pesertaOnsite = Pendaftar::where('mata_lomba_id', $mataLombaId)
            ->where('status_kehadiran', 'Hadir')
            ->count();

        $belumDaftarUlang = $totalPeserta - $pesertaOnsite;

        return view('admin.kehadiran.index', compact(
            'pendaftar',
            'totalPeserta',
            'pesertaOnsite',
            'belumDaftarUlang',
            'mataLombaId'
        ));
    }

    public function showQR($id)
    {
        $pendaftar = Pendaftar::with('peserta', 'mataLomba')->findOrFail($id);
        return view('admin.kehadiran.qr', compact('pendaftar'));
    }

    public function edit($id)
    {
        $pendaftar = Pendaftar::with('peserta', 'mataLomba')->findOrFail($id);
        return view('admin.kehadiran.edit', compact('pendaftar'));
    }

    public function update(Request $request, $id)
    {
        $pendaftar = Pendaftar::findOrFail($id);

        if ($pendaftar->status_kehadiran === 'Hadir') {
            return redirect()->route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id])
                ->with('warning', 'Peserta sudah melakukan kehadiran. Data tidak dapat diubah.');
        }

        $pendaftar->status_kehadiran = $request->input('status');
        $pendaftar->tanggal_kehadiran = now();
        $pendaftar->save();

        return redirect()->route('kehadiran.mata-lomba', ['mataLombaId' => $pendaftar->mata_lomba_id])
            ->with('success', 'Data kehadiran berhasil diperbarui.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new KehadiranExport($request->search), 'kehadiran.xlsx');
    }
}
