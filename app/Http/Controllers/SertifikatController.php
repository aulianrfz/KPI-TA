<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Peserta;
use App\Models\Kuisioner;
use App\Models\SertifikatTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class SertifikatController extends Controller
{
    public function pilihEvent()
    {
        $events = Event::all();
        return view('admin.sertifikat.pilih_event', compact('events'));
    }

    public function uploadForm($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.sertifikat.upload', compact('event'));
    }

    public function uploadTemplate(Request $request, $id)
    {
        $request->validate([
            'nama_file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $event = Event::findOrFail($id);

        $path = $request->file('nama_file')->store('sertifikat_templates', 'public');

        SertifikatTemplate::where('event_id', $id)->delete();

        SertifikatTemplate::create([
            'event_id' => $id,
            'nama_file' => $path,
            'posisi_x' => 0,
            'posisi_y' => 0,
        ]);

        return redirect()->route('sertifikat.atur', $id)->with('success', 'Template berhasil diunggah.');
    }

    public function aturPosisi($id)
    {
        $event = Event::findOrFail($id);
        $template = SertifikatTemplate::where('event_id', $id)->first();

        if (!$template) {
            return redirect()->route('sertifikat.uploadForm', $id)->with('error', 'Template belum diupload.');
        }

        return view('admin.sertifikat.atur', compact('event', 'template'));
    }

    public function simpanPosisi(Request $request, $id)
    {
        $template = SertifikatTemplate::where('event_id', $id)->firstOrFail();
        $template->update([
            'posisi_x' => $request->x,
            'posisi_y' => $request->y,
        ]);

        return redirect()
                ->route('sertifikat.pesertaByEvent', $id)
            ->with('success', 'Posisi berhasil disimpan.');
    }


    public function index()
    {
        $events = Event::with('sertifikatTemplate')->has('sertifikatTemplate')->get();
        return view('admin.sertifikat.index', compact('events'));
    }

    public function pesertaByEvent($eventId)
    {
        $event = Event::findOrFail($eventId);
        $template = SertifikatTemplate::where('event_id', $eventId)->first();
        $kuisionerCount = Kuisioner::where('event_id', $eventId)->count();

        $pesertas = Peserta::with(['jawabanKuisioner.kuisioner', 'pendaftar.mataLomba.kategori.event'])
            ->whereHas('pendaftar.mataLomba.kategori.event', fn($q) => $q->where('id', $eventId))
            // ->where('sertifikat_generated', false)
            ->get()
            ->filter(function ($item) use ($kuisionerCount) {
                return $item->jawabanKuisioner->count() >= $kuisionerCount;
            });

        return view('admin.sertifikat.peserta', compact('event', 'template', 'pesertas'));
    }

    public function generateSingle($pesertaId)
    {
        $peserta = Peserta::with('pendaftar.mataLomba.kategori.event.sertifikatTemplate')->findOrFail($pesertaId);

        $event = $peserta->pendaftar?->mataLomba?->kategori?->event;
        $template = $event?->sertifikatTemplate;

        if (!$template) {
            return back()->with('error', 'Template sertifikat tidak tersedia.');
        }

        $pdf = PDF::loadView('admin.sertifikat.template_pdf', [
            'nama_peserta' => $peserta->nama_peserta,
            'template' => $template,
        ])->setPaper('A4', 'landscape');

        $peserta->sertifikat_generated = true;
        $peserta->save();

        return $pdf->download('sertifikat_' . $peserta->nama_peserta . '.pdf');
    }


}
