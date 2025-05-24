<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Kehadiran;
use App\Models\Bergabung;
use App\Models\Tim;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Pendaftar::with('peserta')
            ->whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '');
        if ($search) {
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%$search%")
                    ->orWhere('nim', 'like', "%$search%")
                    ->orWhere('no_hp', 'like', "%$search%")
                    ->orWhere('institusi', 'like', "%$search%");
            });
        }

        $pendaftarList = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $totalPeserta = Pendaftar::whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '')
            ->count();

        $individuCount = Pendaftar::whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '')
            ->whereHas('peserta', function ($q) {
                $q->where('jenis_peserta', 'Individu');
            })->count();

        $timCount = Bergabung::select('tim_id')
            ->groupBy('tim_id')
            ->get()
            ->filter(function ($group) {
                $anggota = Bergabung::where('tim_id', $group->tim_id)->get();

                foreach ($anggota as $anggotaTim) {
                    $punyaQr = Pendaftar::where('peserta_id', $anggotaTim->peserta_id)
                        ->whereNotNull('url_qrCode')
                        ->where('url_qrCode', '!=', '')
                        ->exists();
                    if (!$punyaQr) {
                        return false;
                    }
                }
                return true;
            })
            ->count();

        $pesertaOnSite = Kehadiran::where('status', 'Hadir')->count();
        $belumDaftarUlang = Pendaftar::whereNotNull('url_qrCode')
                ->where('url_qrCode', '!=', '')
                ->whereDoesntHave('kehadiran')
                ->count();

        return view('admin.dashboard.home', [
            'totalPeserta' => $totalPeserta,
            'individuCount' => $individuCount,
            'timCount' => $timCount,
            'pesertaOnSite' => $pesertaOnSite,
            'belumDaftarUlang' => $belumDaftarUlang,
            'pendaftarList' => $pendaftarList,
            'search' => $search,
        ]);
    }


    //belom beres
    public function verifikasiQR($encryptedId)
    {
        try {
            $id = decrypt($encryptedId);
            Log::info('ID hasil dekripsi QR: ' . $id);
        } catch (\Exception $e) {
            Log::warning('Gagal dekripsi ID QR: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'QR Code tidak valid atau telah dimodifikasi.',
            ], 400);
        }

        $pendaftar = Pendaftar::find($id);

        if (!$pendaftar) {
            Log::warning('Pendaftar tidak ditemukan dengan ID: ' . $id);
            return response()->json([
                'status' => 'error',
                'message' => 'Pendaftar tidak ditemukan.',
            ], 404);
        }

        $sudahHadir = Kehadiran::where('pendaftar_id', $id)->exists();

        if ($sudahHadir) {
            return response()->json([
                'status' => 'info',
                'message' => 'Peserta sudah ditandai hadir sebelumnya.',
            ]);
        }

        Kehadiran::create([
            'pendaftar_id' => $id,
            'tanggal' => now(),
            'status' => 'Hadir',
        ]);

        Log::info("Kehadiran dicatat untuk pendaftar_id: {$id}");

        return response()->json([
            'status' => 'success',
            'message' => 'Kehadiran berhasil dicatat.',
        ]);
    }

    //belom beres
    public function markAsPresent(Request $request)
    {
        try {
            Log::info('Request masuk ke markAsPresent:', $request->all());

            $validator = Validator::make($request->all(), [
                'pendaftar_id' => 'required|exists:pendaftar,id',
            ]);

            if ($validator->fails()) {
                Log::warning('Validasi gagal di markAsPresent:', [
                    'input' => $request->all(),
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $pendaftarId = $request->pendaftar_id;
            Log::info("Cek kehadiran untuk pendaftar_id: {$pendaftarId}");

            $sudahHadir = Kehadiran::where('pendaftar_id', $pendaftarId)->exists();

            if ($sudahHadir) {
                Log::info("Pendaftar ID {$pendaftarId} sudah hadir sebelumnya.");
                return response()->json([
                    'status' => 'info',
                    'message' => 'Peserta sudah ditandai hadir sebelumnya.',
                ]);
            }

            Kehadiran::create([
                'pendaftar_id' => $pendaftarId,
                'tanggal' => now(),
                'status' => 'Hadir',
            ]);

            Log::info("Kehadiran dicatat untuk pendaftar_id: {$pendaftarId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Kehadiran berhasil dicatat.',
            ]);
        } catch (\Exception $e) {
            Log::error('Terjadi exception saat mencatat kehadiran:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencatat kehadiran.',
            ], 500);
        }
    }

    public function showIdentitas($id)
    {
        $pendaftar = Pendaftar::with(['peserta'])
            ->findOrFail($id);

        return view('admin.dashboard.identitas', compact('pendaftar'));
    }

}
