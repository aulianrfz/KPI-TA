<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Exports\PendaftarExport;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Kehadiran;
use App\Models\Bergabung;
use App\Models\Event;
use App\Models\Tim;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;


class LaporanPenjualanController extends Controller
{

    public function pilihEvent()
    {
        $events = Event::all();
        return view('admin.laporanPenjualan.pilih_event', compact('events'));
    }

    public function index(Request $request, $eventId)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'asc');

        $query = Peserta::select(
                'institusi',
                DB::raw('COUNT(*) as total_mahasiswa'),
                DB::raw('COUNT(DISTINCT pendaftar.id) as total_tiket')
            )
            ->join('pendaftar', 'peserta.id', '=', 'pendaftar.peserta_id')
            ->join('mata_lomba', 'pendaftar.mata_lomba_id', '=', 'mata_lomba.id')
            ->join('kategori', 'mata_lomba.kategori_id', '=', 'kategori.id')
            ->join('event', 'kategori.event_id', '=', 'event.id')
            ->where('event.id', $eventId)
            ->groupBy('institusi');

        if ($search) {
            $query->where('institusi', 'like', "%$search%");
        }

        $query->orderBy('institusi', $sort);
        $laporanList = $query->paginate(10)->withQueryString();

        $totalPeserta = Pendaftar::whereHas('mataLomba.kategori.event', function ($q) use ($eventId) {
            $q->where('id', $eventId);
        })->count();

        $provinsiCount = Peserta::whereHas('pendaftar.mataLomba.kategori.event', function ($q) use ($eventId) {
            $q->where('id', $eventId);
        })->distinct()->count('provinsi');

        $institusiCount = Peserta::whereHas('pendaftar.mataLomba.kategori.event', function ($q) use ($eventId) {
            $q->where('id', $eventId);
        })->distinct()->count('institusi');

        return view('admin.laporanPenjualan.index', [
            'laporanList' => $laporanList,
            'totalPeserta' => $totalPeserta,
            'provinsiCount' => $provinsiCount,
            'institusiCount' => $institusiCount,
            'eventId' => $eventId
        ]);
    }

    public function detail($eventId, $institusi, Request $request)
    {
        $institusi = urldecode($institusi);
        $search = $request->input('search');

        $query = Pendaftar::with(['peserta', 'mataLomba.kategori', 'peserta.bergabung.tim'])
            ->whereHas('mataLomba.kategori.event', function ($q) use ($eventId) {
                $q->where('id', $eventId);
            })
            ->whereHas('peserta', function ($q) use ($institusi) {
                $q->where('institusi', $institusi);
            });

        if ($search) {
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%$search%")
                ->orWhere('nim', 'like', "%$search%")
                ->orWhere('no_hp', 'like', "%$search%");
            });
        }

        $pendaftarList = $query->paginate(10)->withQueryString();

        return view('admin.laporanPenjualan.detail', [
            'institusi' => $institusi,
            'pendaftarList' => $pendaftarList,
            'eventId' => $eventId
        ]);
    }


    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $sort = $request->input('sort', 'asc');

    //     $query = Peserta::select(
    //             'institusi',
    //             DB::raw('COUNT(*) as total_mahasiswa'),
    //             DB::raw('COUNT(DISTINCT pendaftar.id) as total_tiket'),
    //         )
    //         ->leftJoin('pendaftar', 'peserta.id', '=', 'pendaftar.peserta_id')
    //         ->groupBy('institusi');

    //     if ($search) {
    //         $query->where('institusi', 'like', "%$search%");
    //     }

    //     $query->orderBy('institusi', $sort);
    //     $laporanList = $query->paginate(10)->withQueryString();

    //     $totalPeserta = Pendaftar::count();
    //     $provinsiCount = Peserta::distinct()->count('provinsi');
    //     $institusiCount = Peserta::distinct()->count('institusi');

    //     return view('admin.laporanPenjualan.index', [
    //         'laporanList' => $laporanList,
    //         'totalPeserta' => $totalPeserta,
    //         'provinsiCount' => $provinsiCount,
    //         'institusiCount' => $institusiCount,
    //     ]);
    // }

    // public function detail($institusi, Request $request)
    // {
    //     $institusi = urldecode($institusi);
    //     $search = $request->input('search');
    //     $query = Pendaftar::with(['peserta', 'mataLomba', 'mataLomba.kategori', 'peserta.bergabung.tim'])
    //         ->whereHas('peserta', function ($q) use ($institusi) {
    //             $q->where('institusi', $institusi);
    //         });

    //     if ($search) {
    //         $query->whereHas('peserta', function ($q) use ($search) {
    //             $q->where('nama_peserta', 'like', "%$search%")
    //                 ->orWhere('nim', 'like', "%$search%")
    //                 ->orWhere('no_hp', 'like', "%$search%");
    //         });
    //     }

    //     $pendaftarList = $query->paginate(10)->withQueryString();

    //     return view('admin.laporanPenjualan.detail', [
    //         'institusi' => $institusi,
    //         'pendaftarList' => $pendaftarList,
    //     ]);
    // }

}
