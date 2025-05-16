<?php

namespace App\Http\Controllers;

use App\Models\KategoriLomba;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategorislomba = KategoriLomba::all();
        return view('admin.crud.kategori.index', compact('kategorislomba'));
    }

    public function create()
    {
        return view('admin.crud.kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        KategoriLomba::create([
            'nama_kategori' => $request->nama_kategori
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function show(KategoriLomba $kategori)
    {
        return view('admin.crud.kategori.show', compact('kategori'));
    }

    public function edit($id)
    {
        $kategori = KategoriLomba::findOrFail($id);
        return view('admin.crud.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, KategoriLomba $kategori)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update([
            'nama_kategori' => $request->nama_kategori
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriLomba $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
