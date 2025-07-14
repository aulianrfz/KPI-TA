<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Membayar;
use App\Models\Pembimbing;
use App\Models\PembayaranPembimbing;
use App\Models\PendaftarPembimbing;
use App\Models\Invoice;
use App\Models\Event;
use App\Mail\QrCodeMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
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
    public function index(Request $request)
    {
        $search = $request->search;
        $eventId = $request->event_id;

        $peserta = Peserta::with(['pendaftar.mataLomba.kategori.event', 'tim', 'membayar.invoice'])
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereDoesntHave('tim')
                    ->orWhereHas('bergabung', function ($q) {
                        $q->where('posisi', 'Ketua');
                    });
            })
            ->get()
            ->map(function ($item) {
                $latest = $item->membayar->sortByDesc('waktu')->first();

                // tambahkan properti langsung ke model
                $item->tipe = 'Peserta';
                $item->nama_kategori = optional($item->pendaftar->mataLomba)->nama_lomba ?? '-';
                $item->event_id = optional($item->pendaftar->mataLomba->kategori)->event_id;
                $item->invoice_id = optional($latest?->invoice)->id ?? '-';
                $item->status_pembayaran = strtolower($latest->status ?? 'belum dibayar');
                return $item;
            });

        $pembimbing = Pembimbing::with(['pendaftaran.event', 'pembayaran.invoice'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($item) {
                $latest = $item->pembayaran->sortByDesc('waktu')->first();

                $item->tipe = 'Pembimbing';
                $item->nama_kategori = optional($item->pendaftaran->first()?->event)->nama_event ?? '-';
                $item->event_id = optional($item->pendaftaran->first()?->event)->id;
                $item->invoice_id = optional($latest?->invoice)->id ?? '-';
                $item->status_pembayaran = strtolower($latest->status ?? 'belum dibayar');
                return $item;
            });

        $pembayaranGabungan = $peserta->merge($pembimbing);

        $pembayaranGabungan = $pembayaranGabungan->filter(function ($item) use ($search, $eventId) {
            $matchSearch = !$search || stripos($item->nama_kategori, $search) !== false || stripos($item->invoice_id, $search) !== false;
            $matchEvent = !$eventId || $item->event_id == $eventId;
            return $matchSearch && $matchEvent;
        })->sortByDesc('created_at');

        $listEvent = \App\Models\Event::all();

        return view('user.pembayaran.index', compact('pembayaranGabungan', 'listEvent'));
    }



    public function bayar($tipe, $id)
    {
        // dd($tipe);
        if ($tipe === 'peserta') {
            $peserta = Peserta::with([
                'mataLomba.kategori',
                'tim.peserta',
                'membayar.invoice'
            ])->findOrFail($id);

            $pembayaranPertama = $peserta->membayar->first();
            $batasWaktu = $peserta->created_at->addDays(3);

            // Cek jika batas pembayaran sudah lewat
            if (now()->gt($batasWaktu)) {
                $invoice = $pembayaranPertama?->invoice;

                if ($invoice) {
                    $pesertaTerkait = Peserta::whereHas('membayar', function ($query) use ($invoice) {
                        $query->where('invoice_id', $invoice->id)
                            ->where(function ($q) {
                                $q->where('status', '!=', 'Sudah Membayar')
                                    ->orWhereNull('status');
                            });
                    })->get();

                    foreach ($pesertaTerkait as $p) {
                        foreach ($p->membayar as $membayar) {
                            if ($membayar->status != 'Sudah Membayar') {
                                $membayar->delete();
                            }
                        }
                        $p->delete();
                    }

                    if (Peserta::whereHas('membayar', fn($q) => $q->where('invoice_id', $invoice->id))->count() === 0) {
                        $invoice->delete();
                    }
                }

                if (!Peserta::find($id)) {
                    return redirect()->route('pembayaran.index')
                        ->with('error', 'Batas waktu pembayaran telah berakhir. Pendaftaran yang belum membayar telah dihapus.');
                }
            }

            if (!$pembayaranPertama || !$pembayaranPertama->invoice) {
                abort(404, 'Invoice tidak ditemukan untuk peserta ini.');
            }

            $invoice = $pembayaranPertama->invoice;
            $pesertaSatuInvoice = Peserta::whereHas('membayar', fn($q) => $q->where('invoice_id', $invoice->id))
                ->with('tim')
                ->get();

            $tim = $peserta->tim->first();
            $jumlah_peserta = $pesertaSatuInvoice->count();
            $batas_pembayaran = $batasWaktu->format('d M Y');
            $mataLomba = $peserta->mataLomba;

            return view('user.pembayaran.detail', compact(
                'tipe',
                'peserta',
                'tim',
                'jumlah_peserta',
                'batas_pembayaran',
                'mataLomba',
                'invoice',
                'pesertaSatuInvoice'
            ));

        } elseif ($tipe === 'pembimbing') {
            $pembimbing = Pembimbing::with(['pendaftaran.event', 'pembayaran.invoice'])->findOrFail($id);
            $pembayaranPertama = $pembimbing->pembayaran->first();
            $invoice = $pembayaranPertama?->invoice;

            if (!$invoice) {
                abort(404, 'Invoice tidak ditemukan untuk pembimbing ini.');
            }

            $jumlah_peserta = 1;
            $batas_pembayaran = $pembimbing->created_at->addDays(3)->format('d M Y');

            return view('user.pembayaran.detail', compact(
                'tipe',
                'pembimbing',
                'jumlah_peserta',
                'batas_pembayaran',
                'invoice'
            ) + [
                'peserta' => null,
                'tim' => null,
                'mataLomba' => null,
                'pesertaSatuInvoice' => collect()
            ]);
        }

        // Jika tipe tidak dikenali
        abort(404, 'Tipe tidak valid.');
    }

    public function uploadBuktiPembayaran(Request $request, $tipe, $id)
    {

        $request->validate([
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $kwitansiIndividu = $request->has('kwitansi_individu');
        $kwitansiCapBasah = $request->has('kwitansi_cap_basah');

        $file = $request->file('bukti');
        $filePath = $file->store('bukti_pembayaran', 'public');

        if ($tipe === 'peserta') {
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
                $invoice = Invoice::create(['total_tagihan' => $biaya]);
            }

            $membayar = Membayar::updateOrCreate(
                [
                    'peserta_id' => $peserta->id,
                    'invoice_id' => $invoice->id,
                ],
                [
                    'bukti_pembayaran' => $filePath,
                    'status' => 'Menunggu Verifikasi',
                    'waktu' => now(),
                    'kwitansi_individu' => $kwitansiIndividu,
                    'kwitansi_cap_basah' => $kwitansiCapBasah,
                ]
            );

        } elseif ($tipe === 'pembimbing') {
            $pembimbing = Pembimbing::with('pendaftaran')->findOrFail($id);

            $invoice = PembayaranPembimbing::where('pembimbing_id', $pembimbing->id)
                ->whereNotNull('invoice_id')
                ->with('invoice')
                ->latest()
                ->first()?->invoice;

            if (!$invoice) {
                $invoice = Invoice::create(['total_tagihan' => 50000]); // atau bisa pakai config/event
            }

            PembayaranPembimbing::updateOrCreate(
                [
                    'pembimbing_id' => $pembimbing->id,
                    'invoice_id' => $invoice->id,
                ],
                [
                    'bukti_pembayaran' => $filePath,
                    'status' => 'Menunggu Verifikasi',
                    'waktu' => now(),
                    'kwitansi_individu' => $kwitansiIndividu,
                    'kwitansi_cap_basah' => $kwitansiCapBasah,
                ]
            );
        } else {
            abort(404, 'Tipe tidak valid.');
        }

        return redirect()->route('pembayaran.index')->with('success', 'Bukti pembayaran berhasil diunggah.');
    }

    public function listEvents()
    {
        $events = Event::latest()->get();
        return view('admin.transaksi.list_event', compact('events'));
    }

    public function byEvent($eventId, Request $request)
    {
        session(['selected_event' => $eventId]);

        $search = $request->input('search');
        $sortOrder = $request->input('sort', 'desc');

        $queryPeserta = Membayar::with(['peserta.pendaftar.mataLomba.kategori.event', 'invoice', 'mataLomba'])
            ->whereHas('peserta.pendaftar.mataLomba.kategori', function ($q) use ($eventId) {
                $q->where('event_id', $eventId);
            })
            ->whereNotIn('status', ['Sudah Membayar', 'Ditolak']);

        if ($search) {
            $queryPeserta->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                    ->orWhere('institusi', 'like', "%{$search}%");
            });
        }

        $queryPeserta->orderBy('waktu', in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');

        $pesertaData = $queryPeserta->get()->map(function ($item) {
            $item->tipe = 'Peserta';
            $item->nama = $item->peserta->nama_peserta ?? '-';
            $item->institusi = $item->peserta->institusi ?? '-';
            $item->invoice_id = $item->invoice->id ?? '-';
            $item->lomba = $item->peserta->pendaftar->mataLomba->nama_lomba ?? '-';
            $item->status = $item->peserta->pendaftar->status ?? 'Pending';
            $item->bukti = $item->bukti_pembayaran;
            return $item;
        });

        // Pembimbing
        $queryPembimbing = PembayaranPembimbing::with(['pembimbing', 'invoice', 'pembimbing.pendaftaran.event'])
            ->whereNotIn('status', ['Sudah Membayar', 'Ditolak'])
            ->whereHas('pembimbing.pendaftaran.event', function ($q) use ($eventId) {
                $q->where('id', $eventId);
            });

        if ($search) {
            $queryPembimbing->whereHas('pembimbing', function ($q) use ($search) {
                $q->where('nama_pembimbing', 'like', "%{$search}%")
                    ->orWhere('institusi', 'like', "%{$search}%");
            });
        }

        $queryPembimbing->orderBy('waktu', in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc');

        $pembimbingData = $queryPembimbing->get()->map(function ($item) {
            $item->tipe = 'Pembimbing';
            $item->nama = $item->pembimbing->nama_lengkap ?? '-';
            $item->institusi = $item->pembimbing->institusi ?? '-';
            $item->invoice_id = $item->invoice->id ?? '-';
            $item->lomba = '-';
            $item->status = $item->pembimbing->status ?? 'Pending';
            $item->bukti = $item->bukti_pembayaran;
            return $item;

        });

        // Gabungkan dan paginasi manual
        $combined = $pesertaData->merge($pembimbingData)->sortByDesc('waktu')->values();
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $paginated = new LengthAwarePaginator(
            $combined->forPage($page, $perPage),
            $combined->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $event = Event::findOrFail($eventId);

        return view('admin.transaksi.konfirmasi_pembayaran', [
            'transaksi' => $paginated,
            'event' => $event,
        ]);
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

        foreach ($ids as $fullId) {
            [$tipe, $id] = explode('|', $fullId);
            $id = (int) $id;

            if ($tipe === 'Peserta') {
                $membayar = Membayar::with('peserta.pendaftar.mataLomba.kategori.event', 'peserta.tim.peserta')->find($id);

                if (!$membayar || !$membayar->peserta)
                    continue;

                $peserta = $membayar->peserta;
                $isKelompok = $peserta->tim->isNotEmpty();
                $isKetua = $peserta->tim->first()?->pivot->posisi === 'Ketua';
                $semuaPeserta = $isKelompok && $isKetua ? $peserta->tim->first()->peserta : collect([$peserta]);

                foreach ($semuaPeserta as $p) {
                    $pendaftar = $p->pendaftar;
                    if (!$pendaftar)
                        continue;

                    if ($action === 'approve') {
                        $encryptedId = encrypt('peserta_' . $pendaftar->id);
                        $qrContent = route('verifikasi.qr', ['id' => $encryptedId]);

                        $result = Builder::create()
                            ->writer(new PngWriter())
                            ->data($qrContent)
                            ->encoding(new Encoding('UTF-8'))
                            ->size(300)
                            ->margin(10)
                            ->build();

                        $eventName = Str::slug($pendaftar->mataLomba->kategori->event->nama_event ?? 'event', '_');
                        $filename = 'qr_codes/pendaftar_' . $pendaftar->id . '_' . $eventName . '.png';

                        Storage::disk('public')->put($filename, $result->getString());
                        $qrRelativePath = 'storage/' . $filename;
                        $qrPath = storage_path('app/public/' . $filename);

                        $pendaftar->update(['url_qrCode' => $qrRelativePath]);

                        if ($p->email) {
                            Mail::to($p->email)->send(new QrCodeMail(
                                $p->nama_peserta,
                                $pendaftar->mataLomba->nama_lomba ?? '-',
                                $pendaftar->mataLomba->kategori->nama_kategori ?? '-',
                                $qrPath
                            ));
                        }
                    }

                    $membayar->update(['status' => $status]);
                }

            } elseif ($tipe === 'Pembimbing') {
                $pembayaran = PembayaranPembimbing::with('pembimbing')->find($id);

                if ($pembayaran && $pembayaran->pembimbing) {
                    if ($action === 'approve') {
                        $pendaftarPembimbing = PendaftarPembimbing::with('event')->where('pembimbing_id', $pembayaran->pembimbing->id)->first();

                        if ($pendaftarPembimbing) {
                            $encryptedId = encrypt('pembimbing_' . $pembayaran->pembimbing->id);
                            $qrContent = route('verifikasi.qr', ['id' => $encryptedId]);

                            $result = Builder::create()
                                ->writer(new PngWriter())
                                ->data($qrContent)
                                ->encoding(new Encoding('UTF-8'))
                                ->size(300)
                                ->margin(10)
                                ->build();

                            $eventName = Str::slug($pendaftarPembimbing->event->nama_event ?? 'event', '_');
                            $filename = 'qr_codes/pembimbing_' . $pembayaran->pembimbing->id . '_' . $eventName . '.png';

                            Storage::disk('public')->put($filename, $result->getString());

                            $qrRelativePath = 'storage/' . $filename;
                            $qrPath = storage_path('app/public/' . $filename);

                            $pendaftarPembimbing->update(['url_qrCode' => $qrRelativePath]);

                            $namaEvent = $pendaftarPembimbing->event->nama_event ?? 'Nama Event';

                            if ($pembayaran->pembimbing->email) {
                                Mail::to($pembayaran->pembimbing->email)->send(new QrCodeMail(
                                    $pembayaran->pembimbing->nama_lengkap,
                                    $namaEvent,
                                    null,
                                    $qrPath
                                ));
                            }
                        }
                    }

                    $pembayaran->update(['status' => $status]);
                }
            }
        }

        return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

}
