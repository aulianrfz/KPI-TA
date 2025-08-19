<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;
use Carbon\Carbon;


class VenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $venues = $query->paginate(10)->withQueryString();

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
            'tanggal_tersedia' => 'nullable|date',
            'waktu_mulai_tersedia' => 'nullable|date_format:H:i',
            'waktu_berakhir_tersedia' => 'nullable|date_format:H:i',
        ]);

        Venue::create($request->only([
            'name',
            'tanggal_tersedia',
            'waktu_mulai_tersedia',
            'waktu_berakhir_tersedia',
        ]));

        return redirect()->route('venue.index')->with('success', 'Venue berhasil ditambahkan.');
    }


    public function edit(Venue $venue)
    {
        return view('venue.edit', compact('venue'));
    }
    public function update(Request $request, Venue $venue)
    {
        $data = $request->all();

        // konversi waktu hanya kalau ada inputnya
        if ($request->filled('waktu_mulai_tersedia')) {
            $data['waktu_mulai_tersedia'] = Carbon::parse($request->input('waktu_mulai_tersedia'))->format('H:i');
        }

        if ($request->filled('waktu_berakhir_tersedia')) {
            $data['waktu_berakhir_tersedia'] = Carbon::parse($request->input('waktu_berakhir_tersedia'))->format('H:i');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tanggal_tersedia' => 'nullable|date',
            'waktu_mulai_tersedia' => 'nullable|date_format:H:i',
            'waktu_berakhir_tersedia' => 'nullable|date_format:H:i|after:waktu_mulai_tersedia',
        ]);

        $venue->update(array_merge($validated, $data));

        return redirect()->route('venue.index')->with('success', 'Venue berhasil diperbarui');
    }


    public function destroy(Venue $venue)
    {
        $venue->delete();
        return redirect()->route('venue.index')->with('success', 'Venue berhasil dihapus');
    }
}
