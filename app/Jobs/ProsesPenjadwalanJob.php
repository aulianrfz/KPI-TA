<?php

namespace App\Jobs;

use App\Models\Peserta;
use App\Models\Tim;
use App\Models\Jadwal;
use App\Models\Agenda;
use App\Models\MataLomba;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;
use App\Http\Controllers\PenjadwalanController;

class ProsesPenjadwalanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startTime, $endTime, $variabelX,
    $pesertaKategori, $constraintTambahan, $jadwalHarian,
    $namaJadwal, $jadwalId, $version;

    public function __construct($startTime, $endTime, $variabelX, $pesertaKategori, $constraintTambahan, $jadwalHarian, $namaJadwal, $jadwalId, $version)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->variabelX = $variabelX;
        $this->pesertaKategori = $pesertaKategori;
        $this->constraintTambahan = $constraintTambahan;
        $this->jadwalHarian = $jadwalHarian;
        $this->namaJadwal = $namaJadwal;
        $this->jadwalId = $jadwalId;
        $this->version = $version;

        Log::info("Job dispatched with data: " . json_encode([
            'constraintTambahan' => $constraintTambahan,
            'endTime' => $endTime,
            'variabelX' => $variabelX,
            'pesertaKategori' => $pesertaKategori,
        ]));
    }

    public function handle()
    {
        Log::info("Running job handle");

        $penjadwal = new PenjadwalanController();

        $domain = $penjadwal->constraintPropagation($this->variabelX, $this->constraintTambahan, $this->jadwalHarian);
        Log::info('Generated domain: ' . json_encode($domain));

        $jadwalValidSolutions = $penjadwal->backtrack($domain);

        Log::info("Selesai backtrack pada job");

        if (!$jadwalValidSolutions) {
            Log::warning("Gagal melakukan penjadwalan. Tidak ada jadwal valid ditemukan.");
            if ($this->version == 1) {
                $jadwalMaster = Jadwal::find($this->jadwalId);
                if ($jadwalMaster) {
                    $jadwalMaster->update(['status' => 'Gagal']);
                }
            }
            return;
        }

        // Jika versi 1, pakai jadwal master, versi selanjutnya buat jadwal baru per solusi
        if ($this->version == 1) {
            $jadwalMaster = Jadwal::find($this->jadwalId);
            if (!$jadwalMaster) {
                Log::error("Jadwal master tidak ditemukan");
                return;
            }
            // Simpan solusi pertama ke jadwal master
            $this->saveAgenda($jadwalMaster, $jadwalValidSolutions[0]);
            $jadwalMaster->update(['status' => 'Selesai']);

            // Simpan solusi lain sebagai versi 2 dan seterusnya
            for ($i = 1; $i < count($jadwalValidSolutions); $i++) {
                $version = $this->version + $i;
                $jadwalBaru = Jadwal::create([
                    'nama_jadwal' => $this->namaJadwal,
                    'tahun' => now()->year,
                    'version' => $version,
                    'status' => 'Menunggu',
                ]);
                $this->saveAgenda($jadwalBaru, $jadwalValidSolutions[$i]);
                $jadwalBaru->update(['status' => 'Selesai']);
            }
        } else {
            // Untuk versi selain 1, berarti ini pemanggilan job lanjutan
            $jadwalMaster = Jadwal::create([
                'nama_jadwal' => $this->namaJadwal,
                'tahun' => now()->year,
                'version' => $this->version,
                'status' => 'Menunggu',
            ]);
            $this->saveAgenda($jadwalMaster, $jadwalValidSolutions[0]);
            $jadwalMaster->update(['status' => 'Selesai']);
        }

        Log::info("Penjadwalan selesai.");
    }

    // Fungsi bantu simpan agenda dari solusi
    private function saveAgenda($jadwalMaster, $jadwalValid)
    {
        foreach ($jadwalValid as $jadwal) {
            if (count($jadwal['peserta']) === 1) {
                $peserta = Peserta::where('nim', $jadwal['peserta'][0])->first();
                $pesertaId = $peserta?->id;
                $timId = null;
            } else {
                $tim = Tim::whereHas('peserta', function ($query) use ($jadwal) {
                    $query->whereIn('nim', $jadwal['peserta']);
                }, '=', count($jadwal['peserta']))->first();

                $pesertaId = null;
                $timId = $tim?->id;
            }

            $mataLomba = MataLomba::where('nama_lomba', $jadwal['kategori_lomba'])->first();

            Agenda::create([
                'jadwal_id' => $jadwalMaster->id,
                'mata_lomba_id' => $mataLomba->id ?? null,
                'waktu_mulai' => $jadwal['waktu_mulai'],
                'waktu_selesai' => $jadwal['waktu_selesai'],
                'tanggal' => $jadwal['tanggal'],
                'venue_id' => $jadwal['venue'],
                'peserta_id' => $pesertaId,
                'tim_id' => $timId,
            ]);
        }
    }
}
