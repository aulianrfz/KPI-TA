<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MataLomba;
use App\Models\Pendaftar;
use App\Models\KategoriLomba;
use App\Models\Event;


class DashboardUserController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_event', 'like', '%' . $request->search . '%')
                ->orWhere('penyelenggara', 'like', '%' . $request->search . '%');
        }

        $events = $query->get();

        return view('landing', compact('events'));
    }

    public function show($id)
    {
        $events = Event::findOrFail($id);
        return view('user.event.show', compact('events'));
    }

    public function showEvent($eventId, Request $request)
    {
        $event = Event::with('kategori')->findOrFail($eventId);

        $categories = $event->kategori;

        if ($request->filled('search')) {
            $categories = $categories->filter(function ($kategori) use ($request) {
                return str_contains(strtolower($kategori->nama_kategori), strtolower($request->search));
            });
        }

        return view('user.event.list', compact('event', 'categories'));
    }

    public function showCategory($kategori_id, Request $request)
    {
        $query = MataLomba::where('kategori_id', $kategori_id);

        if ($request->filled('search')) {
            $query->where('nama_lomba', 'like', '%' . $request->search . '%');
        }

        if ($request->has('jenis_lomba')) {
            $query->whereIn('jenis_lomba', $request->jenis_lomba);
        }

        if ($request->has('biaya_pendaftaran')) {
            $query->where('biaya_pendaftaran', '<=', $request->biaya_pendaftaran);
        }

        $events = $query->get();

        return view('user.event.general', compact('events'));
    }

    public function showDetail($id)
    {
        $events = MataLomba::findOrFail($id);
        $total_pendaftar = Pendaftar::where('mata_lomba_id', $id)->count();
        return view('user.event.showdetail', compact('events', 'total_pendaftar'));
    }
}
