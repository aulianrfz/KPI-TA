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
use App\Models\Tim;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'asc');

        $query = Pendaftar::with('peserta')
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

        $query->orderBy(
            Peserta::select('nama_peserta')
                ->whereColumn('peserta.id', 'pendaftar.peserta_id'),
            $sort
        );

        $pendaftarList = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $totalPeserta = Pendaftar::whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '')
            ->whereHas('membayar', function ($q) {
                $q->where('status', 'Sudah Membayar');
            })
            ->count();

        $individuCount = Pendaftar::whereNotNull('url_qrCode')
            ->where('url_qrCode', '!=', '')
            ->whereHas('membayar', function ($q) {
                $q->where('status', 'Sudah Membayar');
            })
            ->whereHas('peserta', function ($q) {
                $q->where('jenis_peserta', 'Individu');
            })
            ->count();

        $timCount = Bergabung::select('tim_id')
            ->groupBy('tim_id')
            ->get()
            ->filter(function ($group) {
                $anggota = Bergabung::where('tim_id', $group->tim_id)->get();

                foreach ($anggota as $anggotaTim) {
                    $punyaQrDanBayar = Pendaftar::where('peserta_id', $anggotaTim->peserta_id)
                        ->whereNotNull('url_qrCode')
                        ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')")
                        ->exists();

                    if (!$punyaQrDanBayar) {
                        return false;
                    }
                }

                return true;
            })
            ->count();

        $pesertaOnSite = Kehadiran::where('status', 'Hadir')->count();

        $belumDaftarUlang = $totalPeserta - $pesertaOnSite ;

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


    public function markAsPresent(Request $request)
    {
        Log::info('markAsPresent dipanggil dengan data:', $request->all());

        if (!$request->has('id')) {
            return response()->json(['error' => "QR code tidak valid: tidak ada parameter 'id'"], 400);
        }

        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'QR code tidak valid: gagal mendekripsi ID.'], 400);
        }

        $pendaftar = Pendaftar::find($decryptedId);

        if (!$pendaftar) {
            return response()->json(['error' => 'QR code tidak valid: peserta tidak ditemukan.'], 404);
        }

        if ($pendaftar->kehadiran) {
            return response()->json(['message' => 'Peserta sudah ditandai hadir sebelumnya.']);
        }

        $kehadiran = new Kehadiran();
        $kehadiran->pendaftar_id = $pendaftar->id;
        $kehadiran->tanggal = now();
        $kehadiran->status = 'Hadir';
        $kehadiran->save();

        Log::info("Peserta ID {$decryptedId} berhasil ditandai hadir.");

        return response()->json(['message' => 'Peserta berhasil ditandai hadir.']);
    }

    public function showIdentitas($id)
    {
        $pendaftar = Pendaftar::with(['peserta'])
            ->findOrFail($id);

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
