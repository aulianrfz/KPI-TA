<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KategoriLomba;
use App\Models\Venue;
use App\Models\Peserta;
use App\Models\Jadwal;

class PenjadwalanController extends Controller
{
    public function generateSchedule(Request $request)
    {
        $request->validate([
            'nama_jadwal' => 'required|string',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $namaJadwal = $request->input('nama_jadwal');
        $tahun = $request->input('tahun');

        $kategoriLomba = KategoriLomba::all();
        $venues = Venue::all();
        $peserta = Peserta::all();
        
        $waktu = range(7 * 60, 17 * 60, 30); // 07:00 - 17:00 dengan interval 30 menit
        $jadwal = [];

        for ($version = 1; $version <= 2; $version++) {
            $domainJadwal = $this->constraintPropagation($kategoriLomba, $venues, $peserta, $waktu);
            $hasil = [];
            if ($this->backtracking($domainJadwal, $hasil, [])) {
                foreach ($hasil as $entry) {
                    $dataJadwal = Jadwal::create([
                        'nama_jadwal' => $namaJadwal,
                        'tahun' => $tahun,
                        'kategori_lomba' => $entry['kategori_lomba'],
                        'waktu_mulai' => sprintf('%02d:%02d', floor($entry['waktu_mulai'] / 60), $entry['waktu_mulai'] % 60),
                        'waktu_selesai' => sprintf('%02d:%02d', floor($entry['waktu_selesai'] / 60), $entry['waktu_selesai'] % 60),
                        'venue' => $entry['venue'],
                        'peserta' => $entry['peserta'],
                        'version' => $version
                    ]);
                    $jadwal[] = $dataJadwal;
                }
            }
        }

        return response()->json([
            'message' => 'Jadwal berhasil dibuat!',
            'jadwal' => $jadwal
        ]);
    }

    private function constraintPropagation($kategoriLomba, $venues, $peserta, $waktu)
    {
        $domain = [];

        foreach ($kategoriLomba as $lomba) {
            $validSlots = [];
            foreach ($waktu as $mulai) {
                $selesai = $mulai + $lomba->duration;
                foreach ($venues as $venue) {
                    foreach ($peserta as $pesertaTerpilih) {
                        $validSlots[] = [
                            'kategori_lomba' => $lomba->name,
                            'waktu_mulai' => $mulai,
                            'waktu_selesai' => $selesai,
                            'venue' => $venue->name,
                            'peserta' => $pesertaTerpilih->name
                        ];
                    }
                }
            }
            $domain[$lomba->name] = $validSlots;
        }

        return $domain;
    }

    private function backtracking(&$domain, &$result, $assigned)
    {
        if (count($assigned) === count($domain)) {
            $result = $assigned;
            return true;
        }

        foreach ($domain as $kategori => $slots) {
            if (!isset($assigned[$kategori])) {
                foreach ($slots as $slot) {
                    if ($this->isValid($assigned, $slot)) {
                        $assigned[$kategori] = $slot;
                        if ($this->backtracking($domain, $result, $assigned)) {
                            return true;
                        }
                        unset($assigned[$kategori]);
                    }
                }
                return false;
            }
        }

        return false;
    }

    private function isValid($assigned, $slot)
    {
        foreach ($assigned as $entry) {
            if ($entry['venue'] === $slot['venue'] && (
                ($slot['waktu_mulai'] >= $entry['waktu_mulai'] && $slot['waktu_mulai'] < $entry['waktu_selesai']) ||
                ($slot['waktu_selesai'] > $entry['waktu_mulai'] && $slot['waktu_selesai'] <= $entry['waktu_selesai'])
            )) {
                return false;
            }

            if ($entry['peserta'] === $slot['peserta'] && (
                ($slot['waktu_mulai'] >= $entry['waktu_mulai'] && $slot['waktu_mulai'] < $entry['waktu_selesai']) ||
                ($slot['waktu_selesai'] > $entry['waktu_mulai'] && $slot['waktu_selesai'] <= $entry['waktu_selesai'])
            )) {
                return false;
            }
        }
        return true;
    }
}