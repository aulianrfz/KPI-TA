<?php

namespace App\Http\Controllers;

use App\Models\MataLomba;
use App\Models\KategoriLomba;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MataLombaController extends Controller
{
public function index(Request $request)
{
    $kategoriId = $request->input('kategori_id');
    $search = $request->input('search');
    $eventId = null;

    $query = MataLomba::with(['kategori', 'venue']);

    if ($kategoriId) {
        $query->where('kategori_id', $kategoriId);
        $kategori = KategoriLomba::find($kategoriId);
        $eventId = $kategori ? $kategori->event_id : null;
    }

    if ($search) {
        $query->where('nama_lomba', 'like', '%' . $search . '%');
    }

    $mataLombas = $query->paginate(10)->appends($request->only(['kategori_id', 'search']));

    return view('admin.crud.mataLomba.index', compact('mataLombas', 'kategoriId', 'eventId'));
}


public function create(Request $request)
{
    $eventId = $request->input('event_id');

    if (!$eventId) {
        return redirect()->route('kategori.index')->with('warning', 'Silakan pilih event terlebih dahulu.');
    }

    $kategoris = KategoriLomba::with('event')
        ->where('event_id', $eventId)
        ->get();

    $venues = Venue::all();

    return view('admin.crud.mataLomba.create', compact('kategoris', 'venues', 'eventId'));
}


    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'venue_id' => 'nullable|exists:venue,id',
            'nama_lomba' => 'required|string|max:100',
            'jurusan' => 'nullable|string|max:50',
            'min_peserta' => 'required|integer|min:1',
            'maks_peserta' => 'required|integer|min:1',
            'maks_total_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'foto_kompetisi' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'required|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'durasi' => 'required|integer|min:1',
            'is_serentak' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['jenis_lomba'] = $data['maks_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->hasFile('url_tor')) {
            $data['url_tor'] = $request->file('url_tor')->store('tor', 'public');
        }

        if ($request->hasFile('foto_kompetisi')) {
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        }

        mataLomba::create($data);

        return redirect()->route('mataLomba.index', ['kategori_id' => $data['kategori_id']])
            ->with('success', 'Sub Kategori berhasil dibuat.');
    }

    public function show(mataLomba $mataLomba)
    {
        return view('admin.crud.mataLomba.show', compact('mataLomba'));
    }

    public function edit($id)
    {
        $mataLomba = mataLomba::findOrFail($id);
        $kategoris = KategoriLomba::all();
        $venues = Venue::all();
        return view('admin.crud.mataLomba.edit', compact('mataLomba', 'kategoris', 'venues'));
    }

    public function update(Request $request, MataLomba $mataLomba)
    {
        $request->validate([
            'kategori_id' => 'required|exists:kategori,id',
            'venue_id' => 'nullable|exists:venue,id',
            'nama_lomba' => 'required|string|max:100',
            'jurusan' => 'nullable|string|max:50',
            'min_peserta' => 'required|integer|min:1',
            'maks_peserta' => 'required|integer|min:1',
            'maks_total_peserta' => 'required|integer|min:1',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'url_tor' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'foto_kompetisi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_pelaksanaan' => 'required|in:Online,Offline',
            'durasi' => 'required|integer|min:1',
            'is_serentak' => 'required|boolean',
        ]);

        $data = $request->all();
        $data['jenis_lomba'] = $data['maks_peserta'] > 1 ? 'Kelompok' : 'Individu';

        if ($request->filled('hapus_tor') && $mataLomba->url_tor) {
            Storage::disk('public')->delete($mataLomba->url_tor);
            $data['url_tor'] = null;
        } elseif ($request->hasFile('url_tor')) {
            if ($mataLomba->url_tor) {
                Storage::disk('public')->delete($mataLomba->url_tor);
            }
            $data['url_tor'] = $request->file('url_tor')->store('tor', 'public');
        } else {
            unset($data['url_tor']);
        }

        if ($request->hasFile('foto_kompetisi')) {
            if ($mataLomba->foto_kompetisi) {
                Storage::disk('public')->delete($mataLomba->foto_kompetisi);
            }
            $data['foto_kompetisi'] = $request->file('foto_kompetisi')->store('foto_kompetisi', 'public');
        } else {
            unset($data['foto_kompetisi']);
        }

        $mataLomba->update($data);

        return redirect()
            ->route('mataLomba.index', ['kategori_id' => $data['kategori_id']])
            ->with('success', 'Sub Kategori berhasil diperbarui.');
    }


    public function destroy(mataLomba $mataLomba)
    {
        if ($mataLomba->foto_kompetisi) {
            Storage::disk('public')->delete($mataLomba->foto_kompetisi);
        }
        if ($mataLomba->url_tor) {
            Storage::disk('public')->delete($mataLomba->url_tor);
        }
        $mataLomba->delete();

        return redirect()->route('mataLomba.index')->with('success', 'Sub Kategori berhasil dihapus.');
    }
}
