<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembimbing;
use App\Models\PendaftarPembimbing;
use App\Models\PembayaranPembimbing;
use App\Models\Invoice;
use App\Models\Event;
use App\Models\Institusi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PembimbingController extends Controller
{

    public function create(Event $event)
    {
        $institusi = Institusi::all(); // ambil semua data instansi
        return view('user.pendaftaran.formpembimbing', compact('event', 'institusi'));
    }
    public function store(Request $request)
    {
        Log::debug('Mulai proses store pembimbing', $request->all());

        $request->validate([
            'event_id' => 'required|exists:event,id',
            'nama_lengkap' => 'required|string',
            'email' => 'required|email',
            'instansi' => 'required|string',
            'no_hp' => 'required',
            'nip' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'surat_tugas' => 'nullable|file|mimes:pdf,jpg,png',
            'visum' => 'nullable|file|mimes:pdf,jpg,png',
        ]);

        // upload file
        $suratTugasPath = null;
        if ($request->hasFile('surat_tugas')) {
            $suratTugasPath = $request->file('surat_tugas')->store('surat_tugas', 'public');
            Log::debug('Surat tugas berhasil diupload', ['path' => $suratTugasPath]);
        }

        $visumPath = null;
        if ($request->hasFile('visum')) {
            $visumPath = $request->file('visum')->store('visum', 'public');
            Log::debug('Visum berhasil diupload', ['path' => $visumPath]);
        }

        $pembimbing = Pembimbing::create([
            'user_id' => Auth::id(),
            'nama_lengkap' => $request->nama_lengkap,
            'nip' => $request->nip,
            'instansi' => $request->instansi,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'url_surat_tugas' => $suratTugasPath,
            'url_visum' => $visumPath,
        ]);
        Log::debug('Data pembimbing berhasil disimpan', $pembimbing->toArray());

        $pendaftar = PendaftarPembimbing::create([
            'event_id' => $request->event_id,
            'pembimbing_id' => $pembimbing->id,
            'url_qrCode' => null,
            'status_kehadiran' => null,
            'tanggal_kehadiran' => null,
        ]);
        Log::debug('Data pendaftar pembimbing berhasil disimpan', $pendaftar->toArray());

        // Buat invoice dan entri pembayaran default
        $invoice = Invoice::create([
            'total_tagihan' => 50000, // lo bisa ambil dari config atau event jika dinamis
        ]);

        PembayaranPembimbing::create([
            'pembimbing_id' => $pembimbing->id,
            'invoice_id' => $invoice->id,
            'bukti_pembayaran' => null,
            'status' => null,
            'waktu' => now(),
        ]);
        Log::debug('Invoice dan pembayaran pembimbing berhasil dibuat', [
            'invoice_id' => $invoice->id,
            'pembimbing_id' => $pembimbing->id,
        ]);

        return view('user.pendaftaran.berhasil')->with('success', 'Pendaftaran pembimbing berhasil!');

    }



}
