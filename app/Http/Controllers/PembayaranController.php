<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pendaftar;
use App\Models\Peserta;
use App\Models\Membayar;
use App\Models\Invoice;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PembayaranController extends Controller
{
    public function index()
    {
        $pendaftar = Pendaftar::with(['subKategori', 'peserta'])
            ->whereHas('peserta', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->get();

        return view('pembayaran.index', compact('pendaftar'));
    }

    public function bayar($id)
    {
        $peserta = Peserta::with(['subKategori', 'institusi', 'tim'])->findOrFail($id);
        $tim = $peserta->tim->first();
        $jumlah_peserta = $tim ? $tim->peserta()->count() : 1;

        $batas_pembayaran = now()->addDays(3)->format('d M Y');

        return view('pembayaran.detail', [
            'peserta' => $peserta,
            'institusi' => $peserta->institusi,
            'subkategori' => $peserta->subKategori,
            'batas_pembayaran' => $batas_pembayaran,
            'tim' => $tim,
            'jumlah_peserta' => $jumlah_peserta,
        ]);
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
        $query = Membayar::with(['peserta.pendaftar', 'invoice', 'subKategori'])
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

        return view('admin.transaksi', compact('transaksi'));
    }

    

    public function bulkAction(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (!$ids || !in_array($action, ['approve', 'reject'])) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih atau aksi tidak valid.');
        }

        $status = $action == 'approve' ? 'Disetujui' : 'Ditolak';

        foreach ($ids as $id) {
            $pendaftar = Pendaftar::with('peserta')->find($id);

            if (!$pendaftar) continue;

            $updateData = ['status' => $status];

            if ($action === 'approve') {
                $qrContent = route('verifikasi.qr', ['id' => $pendaftar->id]);
                $qrSvg = QrCode::format('svg')->size(300)->generate($qrContent);
                $filename = 'qr_codes/pendaftar_' . $pendaftar->id . '.svg';
                Storage::disk('public')->put($filename, $qrSvg);

                $qrUrl = asset('storage/' . $filename);
                $updateData['url_qrCode'] = $qrUrl;
                $email = $pendaftar->peserta->email ?? null;
                if ($email) {
                    Mail::send('emails.qr_code', [
                        'nama' => $pendaftar->peserta->nama_peserta,
                        'nama_lomba' => $pendaftar->subkategori->nama_lomba
                    ], function ($message) use ($email, $filename) {
                        $message->to($email)
                                ->subject('QR Code Pendaftaran Anda')
                                ->attach(storage_path('app/public/' . $filename)); // <-- Lampiran file QR
                    });

                }
            }

            $pendaftar->update($updateData);
        }

        return redirect()->back()->with('success', 'Status pendaftar berhasil diperbarui.');
    }
}
