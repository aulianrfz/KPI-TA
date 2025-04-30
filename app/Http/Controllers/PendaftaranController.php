<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;  // Tambahkan ini untuk menggunakan Storage facade
use App\Models\SubKategori;
use App\Models\Provinsi;
use App\Models\Jurusan;
use App\Models\Institusi;
use App\Models\Peserta;


class PendaftaranController extends Controller
{
    // Menampilkan form pendaftaran
    public function showForm($id_subkategori)
    {
        $subKategori = SubKategori::findOrFail($id_subkategori);
        $provinsi = Provinsi::all();
        $jurusan = Jurusan::all();
        $institusi = Institusi::all();

        $maksPeserta = $subKategori->maks_peserta;

        return view('pendaftaran.formpeserta', compact('subKategori', 'provinsi', 'jurusan', 'institusi', 'maksPeserta'));
    }

    // Menyimpan data peserta
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'id_subkategori' => 'required|exists:sub_kategori,id',
            'peserta.*.nama' => 'required',
            'peserta.*.nim' => 'required',
            'peserta.*.email' => 'required|email',
            'peserta.*.hp' => 'required',
            'nama_tim' => 'nullable|string|max:255',
            'peserta.*.signature' => 'nullable|string', // validasi base64 signature
        ]);

        // Iterasi peserta yang dikirim dalam array 'peserta'
        foreach ($request->peserta as $key => $peserta) {
            // Menangani file upload untuk KTP
            $ktpPath = null;
            if ($request->hasFile('peserta.' . $key . '.ktp')) {
                // Menyimpan file KTP ke folder 'ktps'
                $ktpPath = $request->file('peserta.' . $key . '.ktp')->store('ktps', 'public');
            }

            // Menangani tanda tangan (signature) dalam format base64
            $ttdPath = null;
            if (!empty($peserta['signature'])) {
                $base64Image = $peserta['signature'];
                // Pastikan format base64 valid dan bukan data kosong
                if (preg_match('/^data:image\/(png|jpeg);base64,/', $base64Image)) {
                    // Menghapus prefix base64 dan spasi
                    $image = str_replace(['data:image/png;base64,', 'data:image/jpeg;base64,'], '', $base64Image);
                    $image = str_replace(' ', '+', $image);

                    // Nama file tanda tangan unik berdasarkan waktu dan string acak
                    $imageName = 'ttd_' . time() . '_' . Str::random(10) . '.png';

                    // Pastikan folder 'uploads/ttd' ada sebelum menyimpan
                    $path = public_path('uploads/ttd');
                    if (!File::exists($path)) {
                        File::makeDirectory($path, 0775, true);
                    }

                    // Menyimpan gambar ke folder 'uploads/ttd/'
                    $imagePath = public_path('uploads/ttd/' . $imageName);
                    File::put($imagePath, base64_decode($image));

                    // Menyimpan path gambar untuk disimpan di database
                    $ttdPath = 'uploads/ttd/' . $imageName;
                }
            }

            // Menyimpan data peserta ke database
            Peserta::create([
                'nama' => $peserta['nama'],
                'nim' => $peserta['nim'],
                'email' => $peserta['email'],
                'hp' => $peserta['hp'],
                'jurusan_id' => $peserta['jurusan_id'] ?? null,
                'provinsi_id' => $peserta['provinsi_id'] ?? null,
                'institusi_id' => $peserta['institusi_id'] ?? null,
                'sub_kategori_id' => $request->id_subkategori, // Ambil dari form
                'user_id' => Auth::id(), 
                'nama_tim' => $request->nama_tim,
                'is_leader' => $key == 0 ? 1 : 0, // Menandakan peserta pertama sebagai leader
                'ktm_path' => $ktpPath,  // Menyimpan path KTP
                'ttd_path' => $ttdPath,  // Menyimpan path tanda tangan
            ]);
        }

        // Redirect atau tampilkan view setelah berhasil mendaftar
        return view('pendaftaran.berhasil')->with('success', 'Pendaftaran berhasil!');
    }

    public function sukses()
    {
        return view('pendaftaran.berhasil');
    }
}

