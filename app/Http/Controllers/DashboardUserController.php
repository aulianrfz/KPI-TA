<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SubKategori;
use App\Models\KategoriLomba;
use App\Models\Event;


class DashboardUserController extends Controller
{
    public function index()
    {
        $events = Event::all();
        return view('landing', compact('events'));
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
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
