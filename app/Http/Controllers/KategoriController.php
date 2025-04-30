<?php

namespace App\Http\Controllers;

use App\Models\KategoriLomba;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategorislomba = KategoriLomba::all();
        return view('crud.kategori.index', compact('kategorislomba'));
    }

    public function create()
    {
        return view('crud.kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        KategoriLomba::create([
            'name' => $request->name
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function show(KategoriLomba $kategori)
    {
        return view('crud.kategori.show', compact('kategori'));
    }

    public function edit($id)
    {
        $kategori = KategoriLomba::findOrFail($id);
        return view('crud.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, KategoriLomba $kategori)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $kategori->update([
            'name' => $request->name
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(KategoriLomba $kategori)
    {
        $kategori->delete();
        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
