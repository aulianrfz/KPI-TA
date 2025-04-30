<?php

namespace App\Http\Controllers;

use App\Models\KategoriLomba;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('landing');
    }

    public function show($id)
    {
        $events = [
            1 => ['title' => 'Kompetisi Pariwisata Indonesia', 'location' => 'Bandung (POLBAN), Indonesia', 'description' => 'Deskripsi acara yang lebih lengkap...'],
        ];

        if (!isset($events[$id])) {
            abort(404);
        }

        $event = $events[$id];
        
        return view('event.show', compact('event'));
    }

    public function showEvent($eventId)
    {
        $event = KategoriLomba::findOrFail($eventId);
        $categories = KategoriLomba::all();
        return view('event.list', compact('event', 'categories'));
    }

}
