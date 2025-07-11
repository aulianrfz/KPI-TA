<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Kuisioner;
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
                $query->whereHas('mataLomba.kategori.event', function ($q) use ($search) {
                    $q->where('nama_event', 'like', '%' . $search . '%');
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
                                    ->from('peserta_tim')
                                    ->whereColumn('peserta_tim.peserta_id', 'peserta.id')
                                    ->where('peserta_tim.posisi', 'Ketua');
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

    public function showDetail($id)
    {
        $pendaftar = Pendaftar::with([
            'mataLomba.kategori.event',
            'peserta.user',
            'peserta.tim.peserta.jawabanKuisioner',
            'peserta.jawabanKuisioner',
        ])->findOrFail($id);

        $eventId = optional($pendaftar->mataLomba?->kategori?->event)->id;

        $kuisionerCount = $eventId
            ? Kuisioner::where('event_id', $eventId)->count()
            : 0;

        return view('user.my-event.detail', compact('pendaftar', 'kuisionerCount'));
    }

    public function isi($pesertaId)
    {
        $peserta = Peserta::with('pendaftar.mataLomba.kategori.event')->findOrFail($pesertaId);
        $kuisioners = Kuisioner::where('event_id', $peserta->pendaftar->mataLomba->kategori->event_id)->get();
        
        $jawabanTersimpan = $peserta->jawabanKuisioner->pluck('jawaban', 'pertanyaan_id');

        return view('user.my-event.kuisioner', compact('peserta', 'kuisioners', 'jawabanTersimpan'));
    }


    public function simpan(Request $request, $pesertaId)
    {
        $peserta = Peserta::findOrFail($pesertaId);
        $kuisioners = Kuisioner::where('event_id', $peserta->pendaftar->mataLomba->kategori->event_id)->get();

        foreach ($kuisioners as $kuisioner) {
            $jawaban = $request->input("jawaban_{$kuisioner->id}");
            if ($jawaban) {
                $peserta->jawabanKuisioner()->updateOrCreate(
                    ['kuisioner_id' => $kuisioner->id],
                    ['jawaban' => $jawaban]
                );
            }
        }

        return redirect()->route('events.list')->with('success', 'Kuisioner berhasil disimpan.');
    }

    
}
