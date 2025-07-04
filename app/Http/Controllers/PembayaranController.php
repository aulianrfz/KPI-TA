<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Membayar;
use App\Models\Invoice;
use App\Models\Event;
use App\Mail\QrCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Support\Facades\Validator;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class PembayaranController extends Controller
{
    public function index()
    {
        $peserta = Peserta::with(['pendaftar.mataLomba', 'tim'])
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereDoesntHave('tim')
                    ->orWhereHas('bergabung', function ($q) {
                        $q->where('posisi', 'Ketua'); 
                    });
            })
            ->paginate(10);

        return view('user.pembayaran.index', compact('peserta'));
    }


    public function bayar($id)
    {
        $peserta = Peserta::with([
            'mataLomba.kategori',
            'tim.peserta',
            'membayar.invoice'
        ])->findOrFail($id);

        $pembayaranPertama = $peserta->membayar->first();
        $batasWaktu = $peserta->created_at->addDays(3);

        if (now()->gt($batasWaktu)) {
            $invoice = $pembayaranPertama?->invoice;

            if ($invoice) {
                $pesertaTerkait = Peserta::whereHas('membayar', function ($query) use ($invoice) {
                    $query->where('invoice_id', $invoice->id)
                        ->where('status', '!=', 'Sudah Membayar')
                        ->orWhereNull('status');
                })->get();

                foreach ($pesertaTerkait as $p) {
                    foreach ($p->membayar as $membayar) {
                        if ($membayar->status != 'Sudah Membayar') {
                            $membayar->delete();
                        }
                    }
                    $p->delete();
                }
                $sisaPeserta = Peserta::whereHas('membayar', function ($query) use ($invoice) {
                    $query->where('invoice_id', $invoice->id);
                })->count();
                if ($sisaPeserta === 0) {
                    $invoice->delete();
                }
            }

            $masihAdaPeserta = Peserta::find($id);
            if (!$masihAdaPeserta) {
                return redirect()->route('pembayaran.index')->with('error', 'Batas waktu pembayaran telah berakhir. Pendaftaran yang belum membayar telah dihapus.');
            }
        }
        if (!$pembayaranPertama || !$pembayaranPertama->invoice) {
            abort(404, 'Invoice tidak ditemukan untuk peserta ini.');
        }

        $invoice = $pembayaranPertama->invoice;
        $pesertaSatuInvoice = Peserta::whereHas('membayar', function ($query) use ($invoice) {
            $query->where('invoice_id', $invoice->id);
        })->with(['tim'])->get();

        $tim = $peserta->tim->first();
        $jumlah_peserta = $pesertaSatuInvoice->count();
        $batas_pembayaran = $peserta->created_at->addDays(3)->format('d M Y');
        $mataLomba = $peserta->mataLomba;

        return view('user.pembayaran.detail', compact(
            'peserta',
            'tim',
            'jumlah_peserta',
            'batas_pembayaran',
            'mataLomba',
            'invoice',
            'pesertaSatuInvoice'
        ));
    }


    public function uploadBuktiPembayaran(Request $request, $id)
    {
        $request->validate([
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $peserta = Peserta::with('pendaftar.mataLomba')->findOrFail($id);
        $mataLomba = $peserta->pendaftar->mataLomba;

        if (!$mataLomba) {
            return back()->with('error', 'Mata lomba tidak ditemukan.');
        }

        $biaya = $mataLomba->biaya_pendaftaran;

        $invoice = Membayar::where('peserta_id', $peserta->id)
            ->whereNotNull('invoice_id')
            ->with('invoice')
            ->latest()
            ->first()?->invoice;

        if (!$invoice) {
            $invoice = Invoice::create([
                'total_tagihan' => $biaya,
            ]);
        }

        $file = $request->file('bukti');
        $filePath = $file->store('bukti_pembayaran', 'public');
        $membayar = Membayar::where('peserta_id', $peserta->id)
            ->where('invoice_id', $invoice->id)
            ->first();

        if ($membayar) {
            $membayar->update([
                'bukti_pembayaran' => $filePath,
                'status' => 'Menunggu Verifikasi',
                'waktu' => now(),
            ]);
        } else {
            Membayar::create([
                'peserta_id' => $peserta->id,
                'invoice_id' => $invoice->id,
                'bukti_pembayaran' => $filePath,
                'status' => 'Menunggu Verifikasi',
                'waktu' => now(),
            ]);
        }

        return redirect()->route('pembayaran.index')->with('success', 'Bukti pembayaran berhasil diunggah.');
    }

    public function listEvents()
    {
        $events = Event::latest()->get();
        return view('admin.transaksi.list_event', compact('events'));
    }

    public function byEvent(Request $request, $eventId)
    {
        session(['selected_event' => $eventId]);
        $search = $request->input('search');
        $sortOrder = $request->input('sort', 'desc');

        $query = Membayar::with(['peserta.pendaftar.mataLomba.kategori.event', 'invoice', 'mataLomba'])
            ->whereHas('peserta.pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->whereNotIn('status', ['Sudah Membayar', 'Ditolak']);

        if ($search) {
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                ->orWhere('institusi', 'like', "%{$search}%");
            });
        }

        $query->orderBy('waktu', in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');

        $transaksi = $query->paginate(20)->appends($request->query());
        $event = Event::findOrFail($eventId);

        return view('admin.transaksi.konfirmasi_pembayaran', compact('transaksi', 'event'));
    }
    // public function show(Request $request)
    // {
    //     $query = Membayar::with(['peserta.pendaftar.mataLomba', 'invoice', 'mataLomba'])
    //         ->whereNotIn('status', ['Sudah Membayar', 'Ditolak']);

    //     if ($request->filled('search')) {
    //         $search = $request->input('search');
    //         $query->whereHas('peserta', function ($q) use ($search) {
    //             $q->where('nama_peserta', 'like', "%{$search}%")
    //             ->orWhere('institusi', 'like', "%{$search}%");
    //         });
    //     }

    //     $sortOrder = $request->input('sort', 'desc');
    //     $query->orderBy('waktu', in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');

    //     $transaksi = $query->paginate(20)->appends($request->query());

    //     return view('admin.transaksi.konfirmasi_pembayaran', compact('transaksi'));
    // }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (!$ids || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih atau aksi tidak valid.');
        }

        $status = $action === 'approve' ? 'Sudah Membayar' : 'Ditolak';

        foreach ($ids as $membayarId) {
            $membayar = Membayar::with('peserta.pendaftar', 'peserta.tim.peserta', 'peserta.mataLomba.kategori')->find($membayarId);
            if (!$membayar || !$membayar->peserta) continue;

            $peserta = $membayar->peserta;
            $isKelompok = $peserta->tim->isNotEmpty();
            $isKetua = $peserta->tim->first()?->pivot->posisi === 'Ketua';
            $semuaPeserta = $isKelompok && $isKetua ? $peserta->tim->first()->peserta : collect([$peserta]);

            foreach ($semuaPeserta as $p) {
                $pendaftar = $p->pendaftar;
                if (!$pendaftar) continue;

                if ($action === 'approve') {
                    $encryptedId = encrypt($pendaftar->id);
                    $qrContent = route('verifikasi.qr', ['id' => $encryptedId]);

                    $result = Builder::create()
                        ->writer(new PngWriter())
                        ->data($qrContent)
                        ->encoding(new Encoding('UTF-8'))
                        ->size(300)
                        ->margin(10)
                        ->build();

                    // $filename = 'qr_codes/pendaftar_' . $pendaftar->id . '.png';
                    // Storage::disk('public')->put($filename, $result->getString());
                    // // $qrPath = storage_path('app/public/' . $filename);
                    // // $qrUrl = asset('storage/' . $filename);
                    // $qrRelativePath = 'storage/' . $filename;

                    $eventName = Str::slug($pendaftar->mataLomba->kategori->event->nama_event, '_'); // slug: "event_abc"
                    $filename = 'qr_codes/pendaftar_' . $pendaftar->id . '_' . $eventName . '.png';

                    Storage::disk('public')->put($filename, $result->getString());
                    $qrRelativePath = 'storage/' . $filename;


                    $pendaftar->update(['url_qrCode' => $qrRelativePath]);

                    if ($p->email) {
                        Mail::to($p->email)->send(new QrCodeMail(
                            $p->nama_peserta,
                            $pendaftar->mataLomba->nama_lomba ?? '-',
                            $pendaftar->mataLomba->kategori->nama_kategori ?? '-',
                            $qrRelativePath
                        ));
                    }
                }

                $membayar->update(['status' => $status]);
            }
        }

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
