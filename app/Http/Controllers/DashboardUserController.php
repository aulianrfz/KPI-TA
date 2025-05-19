<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MataLomba;
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
        $events = Event::findOrFail($id);
        return view('user.event.show', compact('events'));
    }

    public function showEvent($eventId)
    {
        $events = KategoriLomba::findOrFail($eventId);
        $categories = KategoriLomba::all();
        return view('user.event.list', compact('events', 'categories'));
    }

    public function showCategory($kategori_id) 
    {
        $events = MataLomba::where('kategori_id', $kategori_id)->get();
        return view('user.event.general', compact('events'));
    }

    public function showDetail($id)
    {
        $events = MataLomba::findOrFail($id);
        return view('user.event.showdetail', compact('events'));
    }
}
