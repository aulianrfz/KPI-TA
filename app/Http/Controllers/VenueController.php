<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::all();
        return view('venue.index', compact('venues'));
    }

    public function create()
    {
        return view('venue.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Venue::create($request->all());

        return redirect()->route('venue.index')->with('success', 'Venue berhasil ditambahkan');
    }

    public function edit(Venue $venue)
    {
        return view('venue.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $venue->update($request->all());

        return redirect()->route('venue.index')->with('success', 'Venue berhasil diperbarui');
    }

    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('venue.index')->with('success', 'Venue berhasil dihapus');
    }
}
