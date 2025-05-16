<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Tim;
use Carbon\Carbon;

class DashboardAdminController extends Controller
{
    public function index()
    {
        $totalPeserta = Peserta::count();
        $individuCount = Peserta::where('jenis_peserta', 'Individu')->count();
        $timCount = Tim::distinct('nama_tim')->count('nama_tim');
        $pesertaOnSite = Pendaftar::where('status', 'Hadir')->count();
        $belumDaftarUlang = Peserta::whereNotIn('id', Pendaftar::pluck('peserta_id'))->count();

        $pendaftarList = Pendaftar::with('peserta.institusi')
            ->whereNotNull('url_qrCode')
            ->orderByDesc('created_at')
            ->paginate(10);

        $kehadiranPerTanggal = Pendaftar::selectRaw("DATE(created_at) as tanggal, COUNT(*) as total")
            ->where('status', 'Hadir')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderByDesc('tanggal')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => Carbon::parse($item->tanggal)->translatedFormat('l, d F Y'),
                    'jumlah' => $item->total
                ];
            });

        return view('admin.dashboard.home', [
            'totalPeserta' => $totalPeserta,
            'individuCount' => $individuCount,
            'timCount' => $timCount,
            'pesertaOnSite' => $pesertaOnSite,
            'belumDaftarUlang' => $belumDaftarUlang,
            'pendaftarList' => $pendaftarList,
            'kehadiranPerTanggal' => $kehadiranPerTanggal,
        ]);
    }

    public function markAsPresent(Request $request)
    {
        $request->validate([
            'pendaftar_id' => 'required|exists:pendaftar,id',
        ]);

        $pendaftar = Pendaftar::find($request->pendaftar_id);
        $pendaftar->status = 'Hadir';
        $pendaftar->save();

        return response()->json(['success' => true, 'message' => 'Status kehadiran diperbarui.']);
    }
}
