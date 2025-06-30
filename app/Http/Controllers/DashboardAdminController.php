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
use App\Models\Bergabung;
use App\Models\Tim;
use App\Models\Event;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DashboardAdminController extends Controller
{

    public function listEvents()
    {
        $events = Event::latest()->get();
        return view('admin.dashboard.event', compact('events'));
    }

    public function byEvent(Request $request, $eventId)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'desc');

        $query = Pendaftar::with(['peserta', 'mataLomba.kategori.event'])
            ->whereHas('mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->whereNotNull('url_qrCode')
            ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')");

        if ($search) {
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%$search%")
                ->orWhere('nim', 'like', "%$search%")
                ->orWhere('no_hp', 'like', "%$search%")
                ->orWhere('institusi', 'like', "%$search%");
            });
        }

        $pendaftarList = $query->orderBy('created_at', $sort)->paginate(10)->withQueryString();

        $totalPeserta = (clone $query)->count();

        $individuCount = (clone $query)
            ->whereHas('membayar', fn($q) => $q->where('status', 'Sudah Membayar'))
            ->whereHas('peserta', fn($q) => $q->where('jenis_peserta', 'Individu'))
            ->count();

        $timCount = Bergabung::select('tim_id')
            ->groupBy('tim_id')
            ->get()
            ->filter(function ($group) use ($eventId) {
                $anggota = Bergabung::where('tim_id', $group->tim_id)->get();

                foreach ($anggota as $anggotaTim) {
                    $punyaQrDanBayar = Pendaftar::where('peserta_id', $anggotaTim->peserta_id)
                        ->whereHas('mataLomba.kategori', fn($q) => $q->where('event_id', $eventId))
                        ->whereNotNull('url_qrCode')
                        ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')")
                        ->exists();

                    if (!$punyaQrDanBayar) return false;
                }

                return true;
            })
            ->count();

        $pesertaOnSite = (clone $query)->where('status_kehadiran', 'Hadir')->count();
        $belumDaftarUlang = $totalPeserta - $pesertaOnSite;

        $event = Event::findOrFail($eventId);

        return view('admin.dashboard.home', compact(
            'pendaftarList',
            'totalPeserta',
            'individuCount',
            'timCount',
            'pesertaOnSite',
            'belumDaftarUlang',
            'search',
            'event'
        ));
    }

    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $sort = $request->input('sort', 'desc');

    //     $query = Pendaftar::with('peserta', 'mataLomba')
    //         ->whereNotNull('url_qrCode')
    //         ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')");

    //     if ($search) {
    //         $query->whereHas('peserta', function ($q) use ($search) {
    //             $q->where('nama_peserta', 'like', "%$search%")
    //                 ->orWhere('nim', 'like', "%$search%")
    //                 ->orWhere('no_hp', 'like', "%$search%")
    //                 ->orWhere('institusi', 'like', "%$search%");
    //         });
    //     }

    //      $pendaftarList = $query->orderBy('created_at', $sort)->paginate(10)->withQueryString();

    //     $totalPeserta = Pendaftar::whereNotNull('url_qrCode')
    //         ->where('url_qrCode', '!=', '0')
    //         ->count();

    //     $individuCount = Pendaftar::whereNotNull('url_qrCode')
    //         ->where('url_qrCode', '!=', '')
    //         ->whereHas('membayar', function ($q) {
    //             $q->where('status', 'Sudah Membayar');
    //         })
    //         ->whereHas('peserta', function ($q) {
    //             $q->where('jenis_peserta', 'Individu');
    //         })
    //         ->count();

    //     $timCount = Bergabung::select('tim_id')
    //         ->groupBy('tim_id')
    //         ->get()
    //         ->filter(function ($group) {
    //             $anggota = Bergabung::where('tim_id', $group->tim_id)->get();

    //             foreach ($anggota as $anggotaTim) {
    //                 $punyaQrDanBayar = Pendaftar::where('peserta_id', $anggotaTim->peserta_id)
    //                     ->whereNotNull('url_qrCode')
    //                     ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')")
    //                     ->exists();

    //                 if (!$punyaQrDanBayar) {
    //                     return false;
    //                 }
    //             }

    //             return true;
    //         })
    //         ->count();

    //     $pesertaOnSite = Pendaftar::where('status_kehadiran', 'Hadir')->count();
    //     $belumDaftarUlang = $totalPeserta - $pesertaOnSite;

    //     return view('admin.dashboard.home', [
    //         'totalPeserta' => $totalPeserta,
    //         'individuCount' => $individuCount,
    //         'timCount' => $timCount,
    //         'pesertaOnSite' => $pesertaOnSite,
    //         'belumDaftarUlang' => $belumDaftarUlang,
    //         'pendaftarList' => $pendaftarList,
    //         'search' => $search,
    //     ]);
    // }

    public function markAsPresent(Request $request)
    {
        Log::info('markAsPresent dipanggil dengan data:', $request->all());

        if (!$request->has('id')) {
            return response()->json(['error' => "QR code tidak valid: tidak ada parameter 'id'"], 400);
        }

        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (\Exception $e) {
            Log::error('Gagal dekripsi ID QR code: ' . $e->getMessage());
            return response()->json(['error' => 'QR code tidak valid: gagal mendekripsi ID.'], 400);
        }

        $pendaftar = Pendaftar::with('peserta', 'mataLomba')->find($decryptedId);

        if (!$pendaftar) {
            return response()->json(['error' => 'QR code tidak valid: peserta tidak ditemukan.'], 404);
        }

        $urlFotoKtm = $pendaftar->peserta->url_ktm ? asset('storage/' . $pendaftar->peserta->url_ktm) : null;

        if ($pendaftar->status_kehadiran === 'Hadir') {
            return response()->json([
                'message' => 'Peserta sudah ditandai hadir sebelumnya.',
                'nama_peserta' => $pendaftar->peserta->nama_peserta,
                'nama_lomba' => $pendaftar->mataLomba->nama_lomba,
                'foto_ktm' => $urlFotoKtm,
            ]);
        }

        $pendaftar->status_kehadiran = 'Hadir';
        $pendaftar->tanggal_kehadiran = now();
        $pendaftar->save();

        Log::info("Peserta ID {$decryptedId} berhasil ditandai hadir.");

        return response()->json([
            'message' => 'Peserta berhasil ditandai hadir.',
            'nama_peserta' => $pendaftar->peserta->nama_peserta,
            'nama_lomba' => $pendaftar->mataLomba->nama_lomba,
            'foto_ktm' => $urlFotoKtm,
        ]);
    }

    public function showIdentitas($id)
    {
        $pendaftar = Pendaftar::with(['peserta'])->findOrFail($id);
        return view('admin.dashboard.identitas', compact('pendaftar'));
    }

    public function listCrud()
    {
        return view('admin.crud.list');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'asc');

        return Excel::download(new PendaftarExport($search, $sort), 'daftar_peserta.xlsx');
    }
}
