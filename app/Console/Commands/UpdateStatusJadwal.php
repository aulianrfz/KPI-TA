<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Jadwal;
use Carbon\Carbon;

class UpdateStatusJadwal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:update-status-jadwal';
    protected $signature = 'jadwal:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status jadwal menjadi selesai jika sudah lewat waktu_selesai';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jadwals = Jadwal::where('status', 'dijadwalkan')
            ->where('waktu_selesai', '<', Carbon::now())
            ->get();

        foreach ($jadwals as $jadwal) {
            $jadwal->status = 'selesai';
            $jadwal->save();

            $this->info("Status jadwal ID {$jadwal->id} diubah ke selesai");
        }
    }
}
