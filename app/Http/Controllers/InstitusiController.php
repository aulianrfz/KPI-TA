<?php

namespace App\Http\Controllers;

use App\Models\Institusi;
use Illuminate\Http\Request;

class InstitusiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Institusi::query();

        if ($search) {
            $query->where('nama_institusi', 'like', '%' . $search . '%');
        }

        $institusis = $query->paginate(10)->appends(['search' => $search]);

        return view('admin.crud.institusi.index', compact('institusis'));
    }

    public function create()
    {
        return view('admin.crud.institusi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_institusi' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        Institusi::create($request->all());
        return redirect()->route('institusi.index')->with('success', 'Institusi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $institusi = Institusi::findOrFail($id);
        return view('admin.crud.institusi.edit', compact('institusi'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_institusi' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
        ]);

        $institusi = Institusi::findOrFail($id);
        $institusi->update($request->all());

        return redirect()->route('institusi.index')->with('success', 'Institusi berhasil diupdate.');
    }

    public function destroy($id)
    {
        $institusi = Institusi::findOrFail($id);
        $institusi->delete();

        return redirect()->route('institusi.index')->with('success', 'Institusi berhasil dihapus.');
    }
}

