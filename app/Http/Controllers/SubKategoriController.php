<?php

namespace App\Http\Controllers;

use App\Models\SubKategori;
use App\Models\KategoriLomba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class SubKategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategoriId = $request->kategori_id;

        $subkategoris = SubKategori::with('kategori')
            ->when($kategoriId, function ($query, $kategoriId) {
                return $query->where('kategori_id', $kategoriId);
            })
            ->paginate(10)
            ->appends(['kategori_id' => $kategoriId]);

        return view('admin.crud.subkategori.index', compact('subkategoris', 'kategoriId'));
    }



    public function create()
    {
        $kategoris = KategoriLomba::all();
        return view('admin.crud.subkategori.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_lomba' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'maks_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|url',
            'foto_kompetisi' => 'nullable|image|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'durasi' => 'required|integer|min:1',
        ]);


        $data = $request->all();

        $data['jenis_lomba'] = $data['maks_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        SubKategori::create($data);

        return redirect()->route('subkategori.index')->with('success', 'Sub Kategori berhasil dibuat.');
    }

    public function show(SubKategori $subkategori)
    {
        return view('admin.crud.subkategori.show', compact('subkategori'));
    }

    public function edit($id)
    {
        $subKategori = SubKategori::findOrFail($id);
        $kategoris = KategoriLomba::all();
        return view('admin.crud.subkategori.edit', compact('subKategori', 'kategoris'));
    }

    public function update(Request $request, SubKategori $subkategori)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_lomba' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'maks_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|url',
            'foto_kompetisi' => 'nullable|image|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'durasi' => 'required|integer|min:1',
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
