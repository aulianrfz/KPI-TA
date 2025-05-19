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

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
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

        $peserta = Peserta::findOrFail($id);

        $invoice = Membayar::where('peserta_id', $peserta->id)
                    ->whereNotNull('invoice_id')
                    ->with('invoice')
                    ->latest()
                    ->first()?->invoice;

        if (!$invoice) {
            $invoice = Invoice::create([
                'total_tagihan' => 0,
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
        $query = Membayar::with(['peserta.pendaftar', 'invoice', 'mataLomba'])
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

        $transaksi = $query->orderBy('waktu', 'desc')->get();

        return view('admin.transaksi.konfirmasi_pembayaran', compact('transaksi'));
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (!$ids || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih atau aksi tidak valid.');
        }

        $status = $action === 'approve' ? 'Disetujui' : 'Ditolak';

        foreach ($ids as $membayarId) {
            $membayar = Membayar::with('peserta.pendaftar', 'peserta.mataLomba', 'peserta.mataLomba.kategori')->find($membayarId);

            if (!$membayar || !$membayar->peserta || !$membayar->peserta->pendaftar) {
                continue;
            }

            $pendaftar = $membayar->peserta->pendaftar;
            $updateData = ['status' => $status];

            if ($action === 'approve') {
                $qrContent = route('verifikasi.qr', ['id' => $pendaftar->id]);
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

                $email = $membayar->peserta->email ?? null;
                if ($email) {
                    Mail::to($email)->send(new QrCodeMail(
                        $membayar->peserta->nama_peserta,
                        $membayar->peserta->pendaftar->mataLomba->nama_lomba ?? '-',
                        $membayar->peserta->pendaftar->mataLomba->kategori->nama_kategori ?? '-',
                        $qrPath
                    ));
                }
            }

            $pendaftar->update($updateData);
        }

        return redirect()->back()->with('success', 'Status pendaftar berhasil diperbarui.');
    }
}
