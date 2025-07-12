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
        $eventData = Event::findOrFail($event);
        return view('admin.pendaftaran.pilih_tipe', compact('eventData'));
    }

    public function formPeserta($event)
    {
        $eventData = Event::findOrFail($event);

        $pendaftar = Pendaftar::with('peserta')
            ->whereHas('mataLomba.kategori', function ($q) use ($event) {
                $q->where('event_id', $event);
            })
            ->get();

        return view('admin.pendaftaran.tabel_peserta', compact('eventData', 'pendaftar'));
    }

    public function formPendamping($event)
    {
        $eventData = Event::findOrFail($event);

        $pendamping = \App\Models\PendaftarPembimbing::with('pembimbing')
            ->where('event_id', $event)
            ->get();

        return view('admin.pendaftaran.tabel_pendamping', compact('eventData', 'pendamping'));
    }

    public function formSupporter($event)
    {
        $eventData = Event::findOrFail($event);

        $supporter = \App\Models\PendaftarSupporter::with('supporter')
            ->where('event_id', $event)
            ->get();

        return view('admin.pendaftaran.tabel_supporter', compact('eventData', 'supporter'));
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
            'signature' => 'nullable|string',
        ]);

        $peserta = Peserta::findOrFail($id);
        $pendaftar = $peserta->pendaftar;

        if (!$pendaftar || !$pendaftar->mataLomba) {
            return back()->with('error', 'Data pendaftar atau lomba tidak ditemukan.');
        }

        $mataLomba = $pendaftar->mataLomba;
        $eventId = $mataLomba->kategori->event_id;

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

        $peserta->update([
            'nama_peserta' => $request->nama_peserta,
            'nim' => $request->nim,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'institusi' => $request->institusi,
            'prodi' => $request->prodi,
            'provinsi' => $request->provinsi,
            'url_ttd' => $ttdPath,
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

        return view('admin.pendaftaran.edit_pembimbing', compact('pembimbing'));
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

        return view('admin.pendaftaran.edit_supporter', compact('supporter'));
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

