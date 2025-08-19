<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\RekeningPembayaran;

class RekeningPembayaranController extends Controller
{
    public function pilihEvent()
    {
        $events = Event::all();
        return view('admin.crud.rek_pembayaran.pilih_event', compact('events'));
    }

    public function manage(Event $event)
    {
        // cek apakah sudah ada data rekening untuk event ini
        $rekening = RekeningPembayaran::where('event_id', $event->id)->first();

        return view('admin.crud.rek_pembayaran.manage', compact('event', 'rekening'));
    }

    public function storeOrUpdate(Request $request, Event $event)
    {
        $validated = $request->validate([
            'nama_bank' => 'required|string|max:100',
            'no_rekening' => 'required|string|max:50',
            'nama_rekening' => 'required|string|max:100',
        ]);        

        // update jika sudah ada, kalau belum buat baru
        RekeningPembayaran::updateOrCreate(
            ['event_id' => $event->id],
            $validated
        );

        return redirect()->route('rek-pembayaran.manage', $event->id)->with('success', 'Data berhasil disimpan');
    }
}
