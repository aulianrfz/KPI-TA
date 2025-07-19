<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Juri;
use App\Models\MataLomba;

class JuriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $juris = Juri::with('mataLomba')->get();
        return view('juri.index', compact('juris'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subKategoris = MataLomba::all();
        return view('juri.create', compact('subKategoris'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'jabatan' => 'required',
            'mata_lomba_id' => 'required|exists:mata_lomba,id',
        ]);

        Juri::create($request->all());
        return redirect()->route('juri.index')->with('success', 'Data juri berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Juri $juri)
    {
        $subKategoris = MataLomba::all();
        return view('juri.edit', compact('juri', 'subKategoris'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Juri $juri)
    {
        $request->validate([
            'nama' => 'required',
            'jabatan' => 'required',
            'mata_lomba_id' => 'exists:mata_lomba,id',
        ]);

        $juri->update($request->all());
        return redirect()->route('juri.index')->with('success', 'Data juri berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Juri $juri)
    {
        $juri->delete();
        return redirect()->route('juri.index')->with('success', 'Data juri berhasil dihapus');
    }
}
