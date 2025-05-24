<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Membayar;
use App\Models\Invoice;
use App\Mail\QrCodeMail;

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
        $peserta = Peserta::with(['mataLomba.kategori.event', 'tim'])
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->whereDoesntHave('tim')
                    ->orWhereHas('bergabung', function ($q) {
                        $q->where('posisi', 'Ketua'); 
                    });
            })
            ->get();

        return view('user.pembayaran.index', compact('peserta'));
    }

    public function bayar($id)
    {
        $peserta = Peserta::with(['institusi', 'mataLomba.kategori', 'tim.peserta'])->findOrFail($id);
        $tim = $peserta->tim->first();
        $jumlah_peserta = $tim ? $tim->peserta->count() : 1;

        $batas_pembayaran = now()->addDays(3)->format('d M Y');
        $mataLomba = $peserta->mataLomba;

        return view('user.pembayaran.detail', compact(
            'peserta', 'tim', 'jumlah_peserta', 'batas_pembayaran', 'mataLomba'
        ));
    }

    public function uploadBuktiPembayaran(Request $request, $id) 
    {
        $request->validate([
            'bukti' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $peserta = Peserta::with('mataLomba')->findOrFail($id);
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
                'jabatan' => 'Tim ' . $peserta->nama,
            ]);
        }

        $file = $request->file('bukti');
        $filePath = $file->store('bukti_pembayaran', 'public');

        Membayar::create([
            'peserta_id' => $peserta->id,
            'invoice_id' => $invoice->id,
            'bukti_pembayaran' => $filePath,
            'waktu' => now(),
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Bukti pembayaran berhasil diunggah.');
    }


    public function show(Request $request)
    {
        $query = Membayar::with(['peserta.pendaftar.mataLomba', 'invoice', 'mataLomba'])
            ->whereHas('peserta.pendaftar', function ($q) {
                $q->whereNotIn('status', ['Disetujui', 'Ditolak']);
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('peserta', function ($q) use ($search) {
                $q->where('nama_peserta', 'like', "%{$search}%")
                ->orWhere('institusi', 'like', "%{$search}%");
            });
        }

        $sortOrder = $request->input('sort', 'desc');
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy('waktu', $sortOrder);

        $transaksi = $query->paginate(10)->appends($request->query());

        return view('admin.transaksi.konfirmasi_pembayaran', compact('transaksi'));
    }


    //belom enkrip qr coode
    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (!$ids || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih atau aksi tidak valid.');
        }

        $status = $action === 'approve' ? 'Disetujui' : 'Ditolak';

        foreach ($ids as $membayarId) {
            $membayar = Membayar::with('peserta.pendaftar', 'peserta.tim.peserta', 'peserta.mataLomba.kategori')->find($membayarId);
            if (!$membayar || !$membayar->peserta) {
                continue;
            }

            $peserta = $membayar->peserta;
            $isKelompok = $peserta->tim->isNotEmpty();
            $isKetua = $peserta->tim->first()?->pivot->posisi === 'Ketua';
            $semuaPeserta = collect();

            if ($isKelompok && $isKetua) {
                $tim = $peserta->tim->first();
                $semuaPeserta = $tim->peserta;
            } else {
                $semuaPeserta = collect([$peserta]);
            }

            foreach ($semuaPeserta as $p) {
                $pendaftar = $p->pendaftar;

                if (!$pendaftar) continue;

                $updateData = ['status' => $status];

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

                    $filename = 'qr_codes/pendaftar_' . $pendaftar->id . '.png';
                    Storage::disk('public')->put($filename, $result->getString());
                    $qrPath = storage_path('app/public/' . $filename);

                    $updateData['url_qrCode'] = asset('storage/' . $filename);

                    if ($p->email) {
                        Mail::to($p->email)->send(new QrCodeMail(
                            $p->nama_peserta,
                            $pendaftar->mataLomba->nama_lomba ?? '-',
                            $pendaftar->mataLomba->kategori->nama_kategori ?? '-',
                            $qrPath
                        ));
                    }
                }

                $pendaftar->update($updateData);
            }
        }

        return redirect()->back()->with('success', 'Status pendaftar berhasil diperbarui.');
    }
}
