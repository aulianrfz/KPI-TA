<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $jurusans = Jurusan::when($search, function ($query, $search) {
            return $query->where('nama_jurusan', 'like', "%{$search}%");
        })->paginate(10);

        return view('admin.crud.jurusan.index', compact('jurusans', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
        ]);

        Jurusan::create($request->all());

        return redirect()->route('admin.crud.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
        ]);

        $jurusan->update($request->all());

        return redirect()->route('admin.crud.jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();

        return redirect()->route('admin.crud.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
    }
}
