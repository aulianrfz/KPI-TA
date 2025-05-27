<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pendaftar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyEventController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $pendaftarList = Pendaftar::with(['mataLomba.kategori.event', 'peserta'])
            ->whereHas('peserta', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('mataLomba', function ($q) use ($search) {
                    $q->where('nama_lomba', 'like', '%' . $search . '%');
                });
            })
            ->get();

        $groupedByEvent = $pendaftarList->groupBy(function ($item) {
            return optional($item->mataLomba->kategori->event)->id;
        });

        return view('user.my-event.list', compact('groupedByEvent', 'search'));
    }

    public function detailEvent($eventId, Request $request)
    {
        $search = $request->search;

        $pendaftarList = Pendaftar::with(['mataLomba.kategori', 'peserta.tim'])
            ->whereHas('peserta', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where(function ($q) {
                        $q->whereDoesntHave('tim')
                            ->orWhereExists(function ($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('bergabung')
                                    ->whereColumn('bergabung.peserta_id', 'peserta.id')
                                    ->where('bergabung.posisi', 'Ketua');
                            });
                    });
            })
            ->whereHas('mataLomba.kategori.event', function ($query) use ($eventId) {
                $query->where('id', $eventId);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('mataLomba', function ($q) use ($search) {
                    $q->where('nama_lomba', 'like', '%' . $search . '%');
                });
            })
            ->get();

        return view('user.my-event.listcategory', compact('pendaftarList', 'search'));
    }

}
