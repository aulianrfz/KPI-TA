<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Event::query();

        if ($search) {
            $query->where('nama_event', 'like', '%' . $search . '%');
        }

        $events = $query->paginate(10)->appends(['search' => $search]);

        return view('admin.crud.event.index', compact('events'));
    }

    public function create()
    {
        return view('admin.crud.event.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal',
            'tanggal' => 'required|Date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('event_foto', 'public');
        }

        Event::create($validated);

        return redirect()->route('listevent.index')->with('success', 'Event berhasil ditambahkan.');
    }

    public function edit(Event $listevent)
    {
        return view('admin.crud.event.edit', compact('listevent'));
    }

    public function update(Request $request, Event $listevent)
    {
        $validated = $request->validate([
            'nama_event' => 'required|string|max:255',
            'penyelenggara' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal' => 'required|Date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('event_foto', 'public');
        }

        $listevent->update($validated);

        return redirect()->route('listevent.index')->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $listevent)
    {
        $listevent->delete();
        return back()->with('success', 'Event berhasil dihapus.');
    }
}
