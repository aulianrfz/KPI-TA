<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubKategori;
use App\Models\KategoriLomba;


class DashboardUserController extends Controller
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

    public function showCategory($kategori_id) 
    {
        $events = SubKategori::where('kategori_id', $kategori_id)->get();
        return view('event.general', compact('events'));
    }

    public function showDetail($id)
    {
        $event = SubKategori::findOrFail($id);
        return view('event.showdetail', compact('event'));
    }
}
