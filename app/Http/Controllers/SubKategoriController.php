<?php

namespace App\Http\Controllers;

use App\Models\SubKategori;
use App\Models\KategoriLomba;
use Illuminate\Http\Request;

class SubKategoriController extends Controller
{
    public function index()
    {
        $subkategoris = SubKategori::with('kategori')->get();
        return view('crud.subkategori.index', compact('subkategoris'));
    }

    public function create()
    {
        $kategoris = KategoriLomba::all();
        return view('crud.subkategori.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_lomba,id',
            'name_lomba' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'maks_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|url',
            'foto_kompetisi' => 'nullable|image|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'duration' => 'required|integer|min:1',
        ]);


        $data = $request->all();

        // Tentukan jenis lomba otomatis
        $data['jenis_lomba'] = $data['maks_peserta'] > 1 ? 'Kelompok' : 'Individu';

        // Simpan foto jika ada
        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        SubKategori::create($data);

        return redirect()->route('subkategori.index')->with('success', 'Sub Kategori berhasil dibuat.');
    }

    public function show(SubKategori $subkategori)
    {
        return view('crud.subkategori.show', compact('subkategori'));
    }

    public function edit($id)
    {
        $subKategori = SubKategori::findOrFail($id);
        $kategoris = KategoriLomba::all();
        return view('crud.subkategori.edit', compact('subKategori', 'kategoris'));
    }

    public function update(Request $request, SubKategori $subkategori)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori_lomba,id',
            'name_lomba' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'maks_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|url',
            'foto_kompetisi' => 'nullable|image|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'duration' => 'required|integer|min:1',
        ]);

        $data = $request->all();
        $data['jenis_lomba'] = $data['maks_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        $subkategori->update($data);

        return redirect()->route('subkategori.index')->with('success', 'Sub Kategori berhasil diperbarui.');
    }

    public function destroy(SubKategori $subkategori)
    {
        $subkategori->delete();
        return redirect()->route('subkategori.index')->with('success', 'Sub Kategori berhasil dihapus.');
    }
}
