<?php

namespace App\Http\Controllers;
use App\Models\SubKategori;

use Illuminate\Http\Request;

class EventController extends Controller
{
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
