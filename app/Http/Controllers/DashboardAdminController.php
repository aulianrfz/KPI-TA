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
use App\Models\PendaftarSupporter;
use App\Models\PendaftarPembimbing;
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
        session(['selected_event' => $eventId]);
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

                    if (!$punyaQrDanBayar)
                        return false;
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
            Log::info('[MARK AS PRESENT] Masuk ke markAsPresent() dengan data request:', $request->all());

            if (!$request->has('id')) {
                Log::warning('[MARK AS PRESENT] ID tidak ditemukan di request.');
                return response()->json(['error' => "QR code tidak valid: tidak ada parameter 'id'"], 400);
            }

            $selectedEventId = session('selected_event');
            if (!$selectedEventId) {
                Log::warning('[MARK AS PRESENT] Tidak ada event yang dipilih di sesi.');
                return response()->json(['error' => 'QR code tidak valid karena event belum dipilih.'], 400);
            }

            try {
                $decrypted = Crypt::decrypt($request->input('id'));
                Log::info("[MARK AS PRESENT] ID berhasil didekripsi: {$decrypted}");
            } catch (\Exception $e) {
                Log::error('[MARK AS PRESENT] Gagal dekripsi ID QR code: ' . $e->getMessage());
                return response()->json(['error' => 'QR code tidak valid: gagal mendekripsi ID.'], 400);
            }

            // === PESERTA ===
            if (str_starts_with($decrypted, 'peserta_')) {
                $id = (int) str_replace('peserta_', '', $decrypted);
                $pendaftar = Pendaftar::with('peserta', 'mataLomba.kategori.event')->find($id);

                if (!$pendaftar) {
                    Log::warning("[MARK AS PRESENT] Peserta tidak ditemukan: ID {$id}");
                    return response()->json(['error' => 'Data peserta tidak ditemukan.'], 404);
                }

                if ($pendaftar->mataLomba->kategori->event_id != $selectedEventId) {
                    Log::warning("[MARK AS PRESENT] QR peserta bukan dari event ini. Event QR: {$pendaftar->mataLomba->kategori->event_id}, Event aktif: {$selectedEventId}");
                    return response()->json(['error' => 'QR code tidak sesuai dengan event yang dipilih.'], 403);
                }

                Log::info("[MARK AS PRESENT] Ditemukan sebagai PESERTA: {$pendaftar->peserta->nama_peserta}");

                $urlFotoKtm = $pendaftar->peserta->url_ktm ? asset('storage/' . $pendaftar->peserta->url_ktm) : null;

                if ($pendaftar->status_kehadiran === 'Hadir') {
                    return response()->json([
                        'message' => 'Peserta sudah ditandai hadir sebelumnya.',
                        'nama_peserta' => $pendaftar->peserta->nama_peserta,
                        'nama_lomba' => $pendaftar->mataLomba->nama_lomba,
                        'foto_ktm' => $urlFotoKtm,
                    ]);
                }

                $pendaftar->update([
                    'status_kehadiran' => 'Hadir',
                    'tanggal_kehadiran' => now()
                ]);

                return response()->json([
                    'message' => 'Peserta berhasil ditandai hadir.',
                    'nama_peserta' => $pendaftar->peserta->nama_peserta,
                    'nama_lomba' => $pendaftar->mataLomba->nama_lomba,
                    'foto_ktm' => $urlFotoKtm,
                ]);
            }

            // === SUPPORTER ===
            if (str_starts_with($decrypted, 'supporter_')) {
                $id = (int) str_replace('supporter_', '', $decrypted);
                $pendaftarSupporter = PendaftarSupporter::with('supporter', 'event')->find($id);

                if (!$pendaftarSupporter) {
                    Log::warning("[MARK AS PRESENT] Supporter tidak ditemukan: ID {$id}");
                    return response()->json(['error' => 'Data supporter tidak ditemukan.'], 404);
                }

                if ($pendaftarSupporter->event->id != $selectedEventId) {
                    Log::warning("[MARK AS PRESENT] QR supporter bukan dari event ini. Event QR: {$pendaftarSupporter->event->id}, Event aktif: {$selectedEventId}");
                    return response()->json(['error' => 'QR code tidak sesuai dengan event yang dipilih.'], 403);
                }

                Log::info("[MARK AS PRESENT] Ditemukan sebagai SUPPORTER: {$pendaftarSupporter->supporter->nama}");

                if ($pendaftarSupporter->status_kehadiran) {
                    return response()->json([
                        'message' => 'Supporter sudah ditandai hadir sebelumnya.',
                        'nama_peserta' => $pendaftarSupporter->supporter->nama,
                        'nama_lomba' => $pendaftarSupporter->event->nama_event,
                        'foto_ktm' => null,
                    ]);
                }

                $pendaftarSupporter->update([
                    'status_kehadiran' => 'Hadir',
                    'tanggal_kehadiran' => now()
                ]);

                return response()->json([
                    'message' => 'Supporter berhasil ditandai hadir.',
                    'nama_peserta' => $pendaftarSupporter->supporter->nama,
                    'nama_lomba' => $pendaftarSupporter->event->nama_event,
                    'foto_ktm' => null,
                ]);
            }

            // === PEMBIMBING ===
            if (str_starts_with($decrypted, 'pembimbing_')) {
                $id = (int) str_replace('pembimbing_', '', $decrypted);
                $pendaftarPembimbing = PendaftarPembimbing::with('pembimbing', 'event')->find($id);

                if (!$pendaftarPembimbing) {
                    Log::warning("[MARK AS PRESENT] Pembimbing tidak ditemukan: ID {$id}");
                    return response()->json(['error' => 'Data pembimbing tidak ditemukan.'], 404);
                }

                if ($pendaftarPembimbing->event->id != $selectedEventId) {
                    Log::warning("[MARK AS PRESENT] QR pembimbing bukan dari event ini. Event QR: {$pendaftarPembimbing->event->id}, Event aktif: {$selectedEventId}");
                    return response()->json(['error' => 'QR code tidak sesuai dengan event yang dipilih.'], 403);
                }

                Log::info("[MARK AS PRESENT] Ditemukan sebagai PEMBIMBING: {$pendaftarPembimbing->pembimbing->nama_lengkap}");

                if ($pendaftarPembimbing->status_kehadiran) {
                    return response()->json([
                        'message' => 'Pembimbing sudah ditandai hadir sebelumnya.',
                        'nama_peserta' => $pendaftarPembimbing->pembimbing->nama_lengkap,
                        'nama_lomba' => $pendaftarPembimbing->event->nama_event,
                        'foto_ktm' => null,
                    ]);
                }

                $pendaftarPembimbing->update([
                    'status_kehadiran' => 'Hadir',
                    'tanggal_kehadiran' => now()
                ]);

                return response()->json([
                    'message' => 'Pembimbing berhasil ditandai hadir.',
                    'nama_peserta' => $pendaftarPembimbing->pembimbing->nama_lengkap,
                    'nama_lomba' => $pendaftarPembimbing->event->nama_event,
                    'foto_ktm' => null,
                ]);
            }

            Log::warning("[MARK AS PRESENT] Prefix ID tidak valid: {$decrypted}");
            return response()->json(['error' => 'QR code tidak valid: format ID tidak dikenal.'], 400);
        }


    public function showIdentitas($id)
    {
        $pendaftar = Pendaftar::with([
            'peserta.tim',
            'mataLomba.kategori.event'
        ])
            ->where('peserta_id', $id)
            ->firstOrFail();

        $event = $pendaftar->mataLomba->kategori->event ?? null;

        return view('admin.dashboard.identitas', compact('pendaftar', 'event'));
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
