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
    $namaJadwal, $jadwalId, $version, $eventId;

    public $timeout = 0;

    public function __construct($startTime, $endTime, $variabelX, $pesertaKategori, $constraintTambahan, $jadwalHarian, $namaJadwal, $jadwalId, $version, $eventId)
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
        $this->eventId = $eventId;

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

        $startTime = microtime(true);
        Log::info("Mulai proses penjadwalan pada " . now()->toDateTimeString());


        $penjadwal = new PenjadwalanController();

        $domain = $penjadwal->constraintPropagation($this->variabelX, $this->constraintTambahan, $this->jadwalHarian);
        Log::info('Generated domain: ' . json_encode($domain));

        $jadwalValidSolutions = $penjadwal->backtrack($domain, 3, $this->constraintTambahan,$this->jadwalId, $this->jadwalHarian);

        Log::info("Selesai backtrack pada job");

        if (!$jadwalValidSolutions || isset($jadwalValidSolutions['error'])) {
            $alasan = $jadwalValidSolutions['error'] ?? 'Tidak ada solusi valid ditemukan.';

            Log::warning("Gagal penjadwalan: $alasan");

            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            Log::warning("Penjadwalan gagal. Durasi: " . number_format($duration, 2) . " detik");

            if ($this->version == 1) {
                $jadwalMaster = Jadwal::find($this->jadwalId);
                if ($jadwalMaster) {
                    $jadwalMaster->update([
                        'status' => 'Gagal',
                        'alasan_gagal' => $alasan,
                    ]);
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
                    'event_id' => $this->eventId,

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
                'event_id' => $this->eventId,
            ]);
            $this->saveAgenda($jadwalMaster, $jadwalValidSolutions[0]);
            $jadwalMaster->update(['status' => 'Selesai']);
        }

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        Log::info("Penjadwalan selesai pada " . now()->toDateTimeString() . ". Durasi: " . number_format($duration, 2) . " detik");

        Log::info("Penjadwalan selesai.");
    }

    private function saveAgenda($jadwalMaster, $jadwalValid)
    {
        foreach ($jadwalValid as $jadwal) {
            // Log::debug("Memproses jadwal", ['jadwal' => $jadwal]);

            $mataLomba = MataLomba::where('nama_lomba', $jadwal['kategori_lomba'])->first();
            if (!$mataLomba) {
                // Log::warning("MataLomba tidak ditemukan untuk kategori lomba: {$jadwal['kategori_lomba']}");
                continue;
            }

            // Log::debug("MataLomba ditemukan", ['mataLomba' => $mataLomba->toArray()]);

            $isSerentak = $mataLomba->is_serentak;
            $namaTim = $jadwal['nama_tim'] ?? null;

            // Log::debug("Is Serentak", ['is_serentak' => $isSerentak, 'nama_tim' => $namaTim]);

            $agenda = Agenda::create([
                'jadwal_id' => $jadwalMaster->id,
                'mata_lomba_id' => $mataLomba->id,
                'kegiatan' => 'Pelaksanaan',
                'waktu_mulai' => $jadwal['waktu_mulai'],
                'waktu_selesai' => $jadwal['waktu_selesai'],
                'tanggal' => $jadwal['tanggal'],
                'venue_id' => $jadwal['venue'],
                'peserta_id' => null,
                'tim_id' => null,
            ]);

            // setelah create agenda

            if ($isSerentak) {
                if (is_array($namaTim)) {
                    // Serentak & tim
                    // Log::debug("Serentak & tim, menyimpan agenda_tim untuk banyak tim");
                    $timList = Tim::whereIn('nama_tim', $namaTim)->get();

                    if ($timList->isEmpty()) {
                        // Log::warning("Tidak ditemukan tim dengan nama_tim: " . implode(", ", $namaTim));
                    } else {
                        foreach ($timList as $tim) {
                            $agenda->tim()->attach($tim->id);
                            // Log::debug("Tim dilampirkan ke agenda", ['tim_id' => $tim->id]);
                        }
                    }
                    // Karena sudah simpan tim, **tidak perlu simpan peserta**
                } else {
                    // Serentak & individu
                    // Log::debug("Serentak & individu, menyimpan agenda_peserta");
                    $pesertaIds = Peserta::whereIn('nim', $jadwal['peserta'])->pluck('id')->toArray();
                    $agenda->peserta()->attach($pesertaIds);
                }
            } else {
                // Non-serentak
                if ($namaTim) {
                    // Log::debug("Non-serentak tim, cari dan simpan agenda_tim");

                    $tim = Tim::where('nama_tim', $namaTim)->first();
                    if (!$tim) {
                        // Log::warning("Tim tidak ditemukan dengan nama_tim: $namaTim");
                    } else {
                        $agenda->tim()->attach($tim->id);
                        // Jangan simpan peserta jika tim sudah ada
                    }
                } else {
                    // Log::debug("Non-serentak individu, simpan agenda_peserta");

                    // **Tambahkan cek: apakah agenda_tim kosong?**
                    if ($agenda->tim()->count() === 0) {
                        if (count($jadwal['peserta']) === 1) {
                            $peserta = Peserta::where('nim', $jadwal['peserta'][0])->first();
                            if (!$peserta) {
                                // Log::warning("Peserta dengan NIM {$jadwal['peserta'][0]} tidak ditemukan");
                                continue;
                            }
                            $agenda->peserta()->attach($peserta->id);
                        } else {
                            $pesertaIds = Peserta::whereIn('nim', $jadwal['peserta'])->pluck('id')->toArray();
                            $agenda->peserta()->attach($pesertaIds);
                        }
                    }
                }
            }

        }
    }


}
