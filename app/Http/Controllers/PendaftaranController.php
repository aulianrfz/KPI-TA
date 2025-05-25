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

        $maksPeserta = $mataLomba->maks_peserta;

        return view('user.pendaftaran.formpeserta', compact('mataLomba', 'provinsi', 'institusi', 'maksPeserta'));
    }


    //belom handle gak boleh lebih dari 3 kali daftar dan ketua wajib dip[ilih sekali dan gak boleh lebih
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

        $tim = null;
        $invoice = null;

        if ($jenisPeserta === 'Kelompok') {
            $tim = Tim::create([
                'nama_tim' => $request->input('nama_tim'),
            ]);

            $invoice = Invoice::create([
                'total_tagihan' => 50000 * count($request->peserta), // atau sesuai logika tarif
                'jabatan' => 'Ketua / Tim'
            ]);
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

            if (!$peserta instanceof Peserta) {
                throw new \Exception('Peserta creation failed.');
            }

            Pendaftar::create([
                'mata_lomba_id' => $request->id_mataLomba,
                'peserta_id' => $peserta->id,
                'url_qrCode' => null,
                'status' => 'Pending',
            ]);

            if ($tim) {
                $tim->peserta()->attach($peserta->id, ['posisi' => $pesertaData['posisi'] ?? 'Anggota']);
            }

            if ($jenisPeserta === 'Individu') {
                $invoice = Invoice::create([
                    'total_tagihan' => 50000,
                    'jabatan' => 'Individu'
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


    public function sukses()
    {
        return view('user.pendaftaran.berhasil');
    }
}
