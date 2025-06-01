<?php

namespace App\Http\Controllers;

use App\Models\KategoriLomba;
use App\Models\Event;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
   public function index(Request $request)
    {
        $search = $request->input('search');

        $query = KategoriLomba::query();

        if ($search) {
            $query->where('nama_kategori', 'like', '%' . $search . '%');
        }

        $kategorislomba = $query->paginate(10)->appends(['search' => $search]);
        $events = Event::all(); 
        return view('admin.crud.kategori.index', compact('kategorislomba', 'events'));
    }


    public function create()
    {
        $events = Event::all();
        return view('admin.crud.kategori.create', compact('events'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'nama_kategori' => 'required|string|max:255',
        ]);

        KategoriLomba::create([
            'event_id' => $request->event_id,
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
        $events = Event::all();
        return view('admin.crud.kategori.edit', compact('kategori', 'events'));
    }

    public function update(Request $request, KategoriLomba $kategori)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'nama_kategori' => 'required|string|max:255',
        ]);

        $kategori->update([
            'event_id' => $request->event_id,
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
