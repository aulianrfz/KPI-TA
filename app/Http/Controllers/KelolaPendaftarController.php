<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Peserta;
use App\Models\Pendaftar;
use App\Models\Event;
use App\Models\MataLomba;
use App\Models\Tim;
use App\Models\Bergabung;
use App\Models\PendaftarPembimbing;
use App\Models\Pembimbing;
use App\Models\PendaftarSupporter;
use App\Models\Supporter;
use App\Models\Provinsi;
use App\Models\Institusi;
use App\Models\Jurusan;
use App\Models\Invoice;
use App\Models\Membayar;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class KelolaPendaftarController extends Controller
{
    public function pilihEvent()
    {
        $events = Event::all();
        return view('admin.pendaftaran.pilih_event', compact('events'));
    }

    public function pilihTipePendaftar($event)
    {
        session(['selected_event' => $event]);
        $eventData = Event::findOrFail($event);
        return view('admin.pendaftaran.pilih_tipe', compact('eventData'));
    }

    // public function formPeserta($event, Request $request)
    // {
    //     $eventData = Event::findOrFail($event);

    //     $query = Pendaftar::with(['peserta.bergabung', 'peserta.mataLomba.kategori'])
    //         ->whereHas('mataLomba.kategori', function ($q) use ($event) {
    //             $q->where('event_id', $event);
    //         });

    //     if ($request->has('search') && $request->search != '') {
    //         $search = $request->search;
    //         $query->whereHas('peserta', function ($q) use ($search) {
    //             $q->where('nama_peserta', 'like', "%$search%")
    //               ->orWhere('nim', 'like', "%$search%")
    //               ->orWhere('institusi', 'like', "%$search%");
    //         });
    //     }

    //     $sort = $request->input('sort', 'desc');
    //     $pendaftar = $query->orderBy('created_at', $sort)->get();

    //     $total = $pendaftar->count();
    //     $totalIndividu = $pendaftar->filter(fn($p) => $p->peserta?->jenis_peserta === 'Individu')->count();
    //     $totalTim = $pendaftar->filter(fn($p) => $p->peserta?->jenis_peserta === 'Tim')->count();
    //     $sudahBayar = $pendaftar->filter(fn($p) => $p->status_pembayaran === 'Lunas')->count();
    //     $belumBayar = $total - $sudahBayar;

    //     return view('admin.pendaftaran.tabel_peserta', compact(
    //         'eventData', 'pendaftar',
    //         'total', 'totalIndividu', 'totalTim', 'sudahBayar', 'belumBayar', 'sort'
    //     ));
    // }


    public function formPeserta($event, Request $request)
    {
        $eventData = Event::findOrFail($event);

        $query = Pendaftar::with([
            'peserta.bergabung',
            'peserta.mataLomba.kategori',
            'membayar'
        ])
        ->whereHas('mataLomba.kategori', function ($q) use ($event) {
            $q->where('event_id', $event);
        });

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%$search%")
                ->orWhere('nim', 'like', "%$search%")
                ->orWhere('institusi', 'like', "%$search%");
            });
        }

        $sort = $request->input('sort', 'desc');
        $pendaftar = $query->orderBy('created_at', $sort)->get();

        $total = $pendaftar->count();

        $totalIndividu = $pendaftar->filter(function ($p) {
            return optional($p->peserta)->jenis_peserta === 'Individu';
        })->count();

        $sudahBayar = $pendaftar->filter(function ($p) {
            return $p->membayar && $p->membayar->status === 'Sudah Membayar';
        })->count();

        $belumBayar = $total - $sudahBayar;

        $timCount = \App\Models\Bergabung::select('tim_id')
            ->groupBy('tim_id')
            ->get()
            ->filter(function ($group) use ($event) {
                $anggota = \App\Models\Bergabung::where('tim_id', $group->tim_id)->get();

                foreach ($anggota as $anggotaTim) {
                    $punyaPendaftarDanQR = \App\Models\Pendaftar::where('peserta_id', $anggotaTim->peserta_id)
                        ->whereHas('mataLomba.kategori', fn($q) => $q->where('event_id', $event))
                        ->whereNotNull('url_qrCode')
                        ->whereRaw("TRIM(COALESCE(url_qrCode, '')) NOT IN ('', '0', 'null')")
                        ->exists();

                    if (!$punyaPendaftarDanQR) return false;
                }

                return true;
            })->count();

        return view('admin.pendaftaran.tabel_peserta', compact(
            'eventData', 'pendaftar',
            'total', 'totalIndividu', 'timCount',
            'sudahBayar', 'belumBayar', 'sort'
        ));
    }

    public function formPendamping(Request $request, $event)
    {
        $eventData = Event::findOrFail($event);

        $query = PendaftarPembimbing::with('pembimbing')
            ->where('event_id', $event);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('pembimbing', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('nip', 'like', '%' . $search . '%');
            });
        }

        // if ($request->filled('instansi')) {
        //     $instansi = $request->instansi;
        //     $query->whereHas('pembimbing', function ($q) use ($instansi) {
        //         $q->where('instansi', $instansi);
        //     });
        // }

        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $totalPendamping = $query->count();
        $pendamping = $query->paginate(10)->withQueryString();
        $listInstansi = \App\Models\Pembimbing::distinct()->pluck('instansi');

        return view('admin.pendaftaran.tabel_pendamping', compact(
            'eventData',
            'pendamping',
            'totalPendamping',
            'listInstansi'
        ));
    }

    public function formSupporter(Request $request, $event)
    {
        $eventData = Event::findOrFail($event);

        $query = PendaftarSupporter::with('supporter')
            ->where('event_id', $event);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('supporter', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('instansi', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->input('sort', 'desc');
        $query->orderBy('created_at', $sort);

        $totalSupporter = $query->count();
        $supporter = $query->paginate(10)->withQueryString();

        return view('admin.pendaftaran.tabel_supporter', compact(
            'eventData', 'supporter', 'totalSupporter'
        ));
    }



    public function editPeserta($id)
    {
        $peserta = Peserta::findOrFail($id);
        $provinsi = Provinsi::all();
        $institusi = Institusi::all();
        $prodi = Jurusan::all();

        return view('admin.pendaftaran.edit_peserta', compact('peserta', 'provinsi', 'institusi', 'prodi'));
    }

public function updatePeserta(Request $request, $id)
{
    $request->validate([
        'nama_peserta' => 'required|string|max:255',
        'nim' => 'required|string|max:50',
        'email' => 'required|email',
        'no_hp' => 'required|string|max:20',
        'institusi' => 'required|string|max:255',
        'provinsi' => 'required|string|max:100',
        'prodi' => 'required|string|max:100',
        'url_ktm' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        'signature' => 'nullable|string',
    ]);

    $peserta = Peserta::findOrFail($id);
    $pendaftar = $peserta->pendaftar;

    if (!$pendaftar || !$pendaftar->mataLomba) {
        return back()->with('error', 'Data pendaftar atau lomba tidak ditemukan.');
    }

    $mataLomba = $pendaftar->mataLomba;
    $eventId = $mataLomba->kategori->event_id;

    // Validasi maksimal 3 lomba di event yang sama
    $jumlahLombaDiikuti = Peserta::where('nama_peserta', $request->nama_peserta)
        ->where('nim', $request->nim)
        ->where('institusi', $request->institusi)
        ->whereHas('pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        })
        ->where('id', '!=', $peserta->id)
        ->count();

    if ($jumlahLombaDiikuti >= 3) {
        return back()->with('error', 'Peserta ini sudah terdaftar di 3 mata lomba pada event ini.');
    }

    // ====================
    // PROSES KTM (Optional)
    // ====================
    $ktmPath = $peserta->url_ktm;
    if ($request->hasFile('url_ktm')) {
        $file = $request->file('url_ktm');
        $fileName = 'ktm_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        $path = public_path('uploads/ktm');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
        }

        $file->move($path, $fileName);
        $ktmPath = 'uploads/ktm/' . $fileName;
    }

    // ===========================
    // PROSES TANDA TANGAN (Base64)
    // ===========================
    $ttdPath = $peserta->url_ttd;
    if (!empty($request->signature) && preg_match('/^data:image\/(png|jpeg);base64,/', $request->signature)) {
        $image = str_replace(['data:image/png;base64,', 'data:image/jpeg;base64,'], '', $request->signature);
        $image = str_replace(' ', '+', $image);
        $imageName = 'ttd_' . time() . '_' . Str::random(10) . '.png';

        $path = public_path('uploads/ttd');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0775, true);
        }

        $imagePath = public_path('uploads/ttd/' . $imageName);
        File::put($imagePath, base64_decode($image));

        $ttdPath = 'uploads/ttd/' . $imageName;
    }

    // ===================
    // SIMPAN SEMUA UPDATE
    // ===================
    $peserta->update([
        'nama_peserta' => $request->nama_peserta,
        'nim' => $request->nim,
        'email' => $request->email,
        'no_hp' => $request->no_hp,
        'institusi' => $request->institusi,
        'prodi' => $request->prodi,
        'provinsi' => $request->provinsi,
        'url_ttd' => $ttdPath,
        'url_ktm' => $ktmPath,
    ]);

    return redirect()->route('admin.pendaftaran.peserta', $eventId)->with('success', 'Peserta berhasil diperbarui.');
}

    public function hapusPeserta($id)
    {
        $peserta = Peserta::findOrFail($id);
        $bergabung = Bergabung::where('peserta_id', $id)->first();

        if ($bergabung) {
            $posisi = $bergabung->posisi;
            $timId = $bergabung->tim_id;

            if ($posisi === 'Ketua') {
                $anggotaTim = Bergabung::where('tim_id', $timId)->pluck('peserta_id');

                foreach ($anggotaTim as $pesertaTimId) {
                    Peserta::find($pesertaTimId)?->delete();
                }
                Bergabung::where('tim_id', $timId)->delete();

                return back()->with('success', 'Ketua dan seluruh anggota tim berhasil dihapus.');
            } else {
                $pendaftar = Pendaftar::where('peserta_id', $id)->first();
                $mataLomba = $pendaftar?->mataLomba;
                $minPeserta = $mataLomba?->min_peserta ?? 1;

                $jumlahTim = Bergabung::where('tim_id', $timId)->count();

                if ($jumlahTim - 1 < $minPeserta) {
                    return back()->with('error', 'Tidak bisa menghapus. Tim akan kurang dari jumlah minimum peserta: ' . $minPeserta);
                }

                $bergabung->delete();
                $peserta->delete();

                return back()->with('success', 'Peserta berhasil dihapus.');
            }
        }

        $peserta->delete();

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function editPembimbing($id)
    {
        $pendaftaran = PendaftarPembimbing::with('pembimbing')->findOrFail($id);
        $pembimbing = $pendaftaran->pembimbing;
        $institusi = Institusi::all();

        return view('admin.pendaftaran.edit_pembimbing', compact('pembimbing', 'institusi'));
    }

    public function updatePembimbing(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'nullable|string|max:100',
            'instansi' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email',
        ]);

        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->update($request->only([
            'nama_lengkap', 'nip', 'instansi', 'jabatan', 'no_hp', 'email'
        ]));

        $eventId = $pembimbing->pendaftaran()->first()?->event_id;

        return redirect()->route('admin.pendaftaran.pendamping', $eventId)
            ->with('success', 'Data pembimbing berhasil diperbarui.');
    }


    public function editSupporter($id)
    {
        $pendaftaran = PendaftarSupporter::with('supporter')->findOrFail($id);
        $supporter = $pendaftaran->supporter;
        $institusi = Institusi::all();

        return view('admin.pendaftaran.edit_supporter', compact('supporter', 'institusi'));
    }

    public function updateSupporter(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'instansi' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
        ]);

        $supporter = Supporter::findOrFail($id);
        $supporter->update($request->only([
            'nama', 'email', 'instansi', 'no_hp'
        ]));

        $eventId = $supporter->pendaftaran()->first()?->event_id;

        return redirect()->route('admin.pendaftaran.supporter', $eventId)
            ->with('success', 'Data supporter berhasil diperbarui.');
    }


    public function destroyPembimbing($id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();

        return back()->with('success', 'Pendamping berhasil dihapus.');
    }

    public function destroySupporter($id)
    {
        $supporter = Supporter::findOrFail($id);
        $supporter->delete();

        return back()->with('success', 'Supporter berhasil dihapus.');
    }

}

