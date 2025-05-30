<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HapusPendaftaranKadaluarsa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hapus-pendaftaran-kadaluarsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
// app/Console/Commands/HapusPendaftaranKadaluarsa.php

    public function handle()
    {
        $pesertas = Peserta::with('membayar.invoice')
            ->where('created_at', '<', now()->subDays(3))
            ->whereDoesntHave('membayar', function ($query) {
                $query->whereHas('buktiPembayaran'); // misalnya jika ada relasi bukti pembayaran
            })
            ->get();

        foreach ($pesertas as $peserta) {
            $invoice = $peserta->membayar->first()?->invoice;
            if ($invoice) {
                // Hapus semua peserta di invoice yang sama
                $pesertaTerkait = Peserta::whereHas('membayar', function ($q) use ($invoice) {
                    $q->where('invoice_id', $invoice->id);
                })->get();

                foreach ($pesertaTerkait as $p) {
                    $p->delete();
                }

                $invoice->delete();
                $peserta->membayar->first()?->delete();
            }
        }

        $this->info("Pendaftaran kedaluwarsa berhasil dihapus.");
    }

}
