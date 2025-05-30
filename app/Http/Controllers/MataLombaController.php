<?php

namespace App\Http\Controllers;

use App\Models\mataLomba;
use App\Models\KategoriLomba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class MataLombaController extends Controller
{
    public function index(Request $request)
    {
        $kategoriId = $request->input('kategori_id');
        $search = $request->input('search');

        $query = mataLomba::with('kategori');

        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }

        if ($search) {
            $query->where('nama_lomba', 'like', '%' . $search . '%');
        }

        $mataLombas = $query->paginate(10)->appends($request->only(['kategori_id', 'search']));

        return view('admin.crud.mataLomba.index', compact('mataLombas', 'kategoriId'));
    }


    public function create()
    {
        $kategoris = KategoriLomba::all();
        return view('admin.crud.mataLomba.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'nama_lomba' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'min_peserta' => 'required|integer|min:1',
            'maks_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|url',
            'foto_kompetisi' => 'nullable|image|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'durasi' => 'required|integer|min:1',
        ]);


        $data = $request->all();

        $data['jenis_lomba'] = $data['min_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        mataLomba::create($data);

        return redirect()->route('mataLomba.index', ['kategori_id' => $data['kategori_id']])
            ->with('success', 'Sub Kategori berhasil dibuat.');
    }

    public function show(mataLomba $mataLomba)
    {
        return view('admin.crud.mataLomba.show', compact('mataLomba'));
    }

    public function edit($id)
    {
        $mataLomba = mataLomba::findOrFail($id);
        $kategoris = KategoriLomba::all();
        return view('admin.crud.mataLomba.edit', compact('mataLomba', 'kategoris'));
    }

    public function update(Request $request, mataLomba $mataLomba)
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
        $data['jenis_lomba'] = $data['min_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        $mataLomba->update($data);

        return redirect()->route('mataLomba.index', ['kategori_id' => $data['kategori_id']])
            ->with('success', 'Sub Kategori berhasil dibuat.');
    }

    public function destroy(mataLomba $mataLomba)
    {
        $mataLomba->delete();
        return redirect()->route('mataLomba.index')->with('success', 'Sub Kategori berhasil dihapus.');
    }
}
