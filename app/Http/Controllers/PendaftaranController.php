<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\MataLomba;
use App\Models\Provinsi;
use App\Models\Institusi;
use App\Models\Peserta;
use App\Models\Jurusan;
use App\Models\Invoice;
use App\Models\Membayar;
use App\Models\Pendaftar;
use App\Models\Tim;

class PendaftaranController extends Controller
{
    public function showForm($id_mataLomba)
    {
        $mataLomba = MataLomba::findOrFail($id_mataLomba);
        $provinsi = Provinsi::all();
        $institusi = Institusi::all();
        $prodi = Jurusan::all();

        $maksPeserta = $mataLomba->maks_peserta;

        return view('user.pendaftaran.formpeserta', compact('mataLomba', 'provinsi', 'institusi', 'maksPeserta', 'prodi'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'id_mataLomba' => 'required|exists:mata_lomba,id',
            'peserta.*.nama_peserta' => 'required',
            'peserta.*.nim' => 'required',
            'peserta.*.email' => 'required|email',
            'peserta.*.no_hp' => 'required',
            'peserta.*.signature' => 'nullable|string',
        ]);

        $mataLomba = MataLomba::findOrFail($request->id_mataLomba);
        $jenisPeserta = $mataLomba->maks_peserta == 1 ? 'Individu' : 'Kelompok';

        if ($jenisPeserta === 'Kelompok') {
            $jumlahKetua = collect($request->peserta)
                ->pluck('posisi')
                ->filter(fn($posisi) => strtolower($posisi) === 'ketua')
                ->count();

            if ($jumlahKetua !== 1) {
                return back()->withInput()->with('error', 'Harus ada tepat satu Ketua dalam tim.');
            }
        }

        $institusi = $request->peserta[0]['institusi'] ?? null;

        if (!$institusi) {
            return back()->withInput()->with('error', 'Institusi wajib diisi.');
        }

        if ($jenisPeserta === 'Kelompok') {
            $timSudahAda = Tim::whereHas('peserta', function ($q) use ($institusi, $request) {
                $q->where('institusi', $institusi)
                ->whereHas('pendaftar', function ($p) use ($request) {
                    $p->where('mata_lomba_id', $request->id_mataLomba);
                });
            })->distinct()->count();

            if ($timSudahAda >= 3) {
                return back()->withInput()->with('error', 'Institusi ini sudah mendaftarkan maksimal 3 tim untuk mata lomba ini.');
            }
        } else {
            $individuTerdaftar = Peserta::where('institusi', $institusi)
                ->where('jenis_peserta', 'Individu')
                ->whereHas('pendaftar', function ($q) use ($request) {
                    $q->where('mata_lomba_id', $request->id_mataLomba);
                })->count();

            if ($individuTerdaftar >= 3) {
                return back()->withInput()->with('error', 'Institusi ini sudah mendaftarkan maksimal 3 individu untuk mata lomba ini.');
            }
        }

        $tim = null;
        $invoice = null;

        if ($jenisPeserta === 'Kelompok') {
            $tim = Tim::create([
                'nama_tim' => $request->input('nama_tim'),
            ]);

            $invoice = Invoice::create([
                'total_tagihan' => $mataLomba->biaya_pendaftaran,
                'jabatan' => 'Ketua / Tim',
            ]);
        }

        foreach ($request->peserta as $pesertaData) {
        $jumlahLombaDiikuti = Peserta::where('nama_peserta', $pesertaData['nama_peserta'])
            ->where('nim', $pesertaData['nim'])
            ->where('institusi', $pesertaData['institusi'] ?? null)
            ->whereHas('pendaftar')
            ->count();

        if ($jumlahLombaDiikuti >= 3) {
            return back()->withInput()->with('error', "Peserta {$pesertaData['nama_peserta']} sudah terdaftar di 3 mata lomba.");
        }
    }

        foreach ($request->peserta as $key => $pesertaData) {
            $ktpPath = null;
            if ($request->hasFile('peserta.' . $key . '.ktp')) {
                $ktpPath = $request->file('peserta.' . $key . '.ktp')->store('ktps', 'public');
            }

            $ttdPath = null;
            if (!empty($pesertaData['signature'])) {
                $base64Image = $pesertaData['signature'];
                if (preg_match('/^data:image\/(png|jpeg);base64,/', $base64Image)) {
                    $image = str_replace(['data:image/png;base64,', 'data:image/jpeg;base64,'], '', $base64Image);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'ttd_' . time() . '_' . Str::random(10) . '.png';

                    $path = public_path('uploads/ttd');
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0775, true);
                    }

                    $imagePath = public_path('uploads/ttd/' . $imageName);
                    File::put($imagePath, base64_decode($image));

                    $ttdPath = 'uploads/ttd/' . $imageName;
                }
            }

            $peserta = Peserta::create([
                'nama_peserta' => $pesertaData['nama_peserta'],
                'nim' => $pesertaData['nim'],
                'email' => $pesertaData['email'],
                'no_hp' => $pesertaData['no_hp'],
                'prodi' => $pesertaData['prodi'] ?? null,
                'provinsi' => $pesertaData['provinsi'] ?? null,
                'institusi' => $pesertaData['institusi'] ?? null,
                'user_id' => Auth::id(),
                'jenis_peserta' => $jenisPeserta,
                'url_ktm' => $ktpPath,
                'url_ttd' => $ttdPath,
            ]);

            Pendaftar::create([
                'mata_lomba_id' => $request->id_mataLomba,
                'peserta_id' => $peserta->id,
                'url_qrCode' => 0,
            ]);

            if ($tim) {
                $tim->peserta()->attach($peserta->id, ['posisi' => $pesertaData['posisi'] ?? 'Anggota']);
            }

            if ($jenisPeserta === 'Individu') {
                $invoice = Invoice::create([
                    'total_tagihan' => $mataLomba->biaya_pendaftaran,
                    'jabatan' => 'Individu',
                ]);
            }

            Membayar::create([
                'peserta_id' => $peserta->id,
                'invoice_id' => $invoice->id,
                'bukti_pembayaran' => null,
            ]);
        }

        return view('user.pendaftaran.berhasil')->with('success', 'Pendaftaran berhasil!');
    }



    // public function sukses()
    // {
    //     return view('user.pendaftaran.berhasil');
    // }
}
