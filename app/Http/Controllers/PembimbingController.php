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
use App\Mail\QrCodeMail;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Generator;
use BaconQrCode\Renderer\Image\Png;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        $event = Event::findOrFail($request->event_id);

        $suratTugasPath = null;
        if ($request->hasFile('surat_tugas')) {
            $suratTugasPath = $request->file('surat_tugas')->store('surat_tugas', 'public');
        }

        $visumPath = null;
        if ($request->hasFile('visum')) {
            $visumPath = $request->file('visum')->store('visum', 'public');
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

        $pendaftar = PendaftarPembimbing::create([
            'event_id' => $event->id,
            'pembimbing_id' => $pembimbing->id,
            'url_qrCode' => null,
            'status_kehadiran' => null,
            'tanggal_kehadiran' => null,
        ]);

        // Generate QR
        $encryptedId = encrypt('pembimbing_' . $pendaftar->id);
        $qrContent = route('verifikasi.qr', ['id' => $encryptedId]);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->build();

        $eventName = Str::slug($event->nama_event ?? 'event', '_');
        $filename = 'qr_codes/pembimbing_' . $pembimbing->id . '_' . $eventName . '.png';
        Storage::disk('public')->put($filename, $result->getString());
        $qrRelativePath = 'storage/' . $filename;
        $qrPath = storage_path('app/public/' . $filename);

        $pendaftar->update(['url_qrCode' => $qrRelativePath]);

        if ($event->biaya > 0) {
            // Buat invoice dan pembayaran
            $invoice = Invoice::create([
                'total_tagihan' => $event->biaya,
            ]);

            PembayaranPembimbing::create([
                'pembimbing_id' => $pembimbing->id,
                'invoice_id' => $invoice->id,
                'bukti_pembayaran' => null,
                'status' => null,
                'waktu' => now(),
            ]);
            Log::debug('Invoice dan pembayaran pembimbing berhasil dibuat');
        } else {
            // langsung kirim email QR
            Mail::to($pembimbing->email)->send(new QrCodeMail(
                $pembimbing->nama_lengkap,
                $event->nama_event,
                null,
                $qrPath
            ));
            Log::debug('Email QR pembimbing berhasil dikirim tanpa invoice karena event gratis');
        }

        return view('user.pendaftaran.berhasil')->with('success', 'Pendaftaran berhasil!');
    }
}