<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\SubKategori;
use App\Models\Provinsi;
use App\Models\Jurusan;
use App\Models\Institusi;
use App\Models\Peserta;


class PendaftaranController extends Controller
{
    public function showForm($id_subkategori)
    {
        $subKategori = SubKategori::findOrFail($id_subkategori);
        $provinsi = Provinsi::all();
        $jurusan = Jurusan::all();
        $institusi = Institusi::all();

        $maksPeserta = $subKategori->maks_peserta;

        return view('pendaftaran.formpeserta', compact('subKategori', 'provinsi', 'jurusan', 'institusi', 'maksPeserta'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_subkategori' => 'required|exists:sub_kategori,id',
            'peserta.*.nama' => 'required',
            'peserta.*.nim' => 'required',
            'peserta.*.email' => 'required|email',
            'peserta.*.hp' => 'required',
            'nama_tim' => 'nullable|string|max:255',
            'peserta.*.signature' => 'nullable|string',
        ]);

        foreach ($request->peserta as $key => $peserta) {
            $ktpPath = null;
            if ($request->hasFile('peserta.' . $key . '.ktp')) {
                $ktpPath = $request->file('peserta.' . $key . '.ktp')->store('ktps', 'public');
            }

            $ttdPath = null;
            if (!empty($peserta['signature'])) {
                $base64Image = $peserta['signature'];
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

            Peserta::create([
                'nama' => $peserta['nama'],
                'nim' => $peserta['nim'],
                'email' => $peserta['email'],
                'hp' => $peserta['hp'],
                'jurusan_id' => $peserta['jurusan_id'] ?? null,
                'provinsi_id' => $peserta['provinsi_id'] ?? null,
                'institusi_id' => $peserta['institusi_id'] ?? null,
                'sub_kategori_id' => $request->id_subkategori,
                'user_id' => Auth::id(), 
                'nama_tim' => $request->nama_tim,
                'is_leader' => $key == 0 ? 1 : 0,
                'ktm_path' => $ktpPath,
                'ttd_path' => $ttdPath,
            ]);
        }

        return view('pendaftaran.berhasil')->with('success', 'Pendaftaran berhasil!');
    }

    public function sukses()
    {
        return view('pendaftaran.berhasil');
    }
}

