<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Supporter;
use App\Models\PendaftarSupporter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Institusi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\QrCodeMail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;

class SupporterController extends Controller
{
    public function create($eventId)
    {
        $event = Event::findOrFail($eventId);
        $institusi = Institusi::all();
        return view('user.pendaftaran.formsupporter', compact('event', 'institusi'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:event,id',
            'nama' => 'required|string',
            'email' => 'required|email',
            'instansi' => 'required|string',
            'no_hp' => 'required|string',
        ]);

        $supporter = Supporter::create([
            'user_id' => Auth::id(),
            'nama' => $request->nama,
            'email' => $request->email,
            'instansi' => $request->instansi,
            'no_hp' => $request->no_hp,
        ]);

        $pendaftarSupporter = PendaftarSupporter::create([
            'event_id' => $request->event_id,
            'supporter_id' => $supporter->id,
            'url_qrCode' => null,
            'status_kehadiran' => false,
        ]);

        // generate QR
        $encryptedId = encrypt('supporter_' . $pendaftarSupporter->id);
        $qrContent = route('verifikasi.qr', ['id' => $encryptedId]);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->build();

        $eventName = Str::slug($supporter->event->nama_event ?? 'event', '_');
        $filename = 'qr_codes/supporter_' . $supporter->id . '_' . $eventName . '.png';

        Storage::disk('public')->put($filename, $result->getString());

        $qrRelativePath = 'storage/' . $filename;
        $qrPath = storage_path('app/public/' . $filename); // buat email

        $supporter->update(['url_qrCode' => $qrRelativePath]);

        // ambil nama event
        $event = Event::find($request->event_id);

        // kirim email
        Mail::to($supporter->email)->send(new QrCodeMail(
            $supporter->nama,
            $event->nama_event, // jadi $nama_utama
            null,               // kategori null untuk supporter
            $qrPath
        ));


        return view('user.pendaftaran.berhasil')->with('success', 'Pendaftaran supporter berhasil!');
    }
}
