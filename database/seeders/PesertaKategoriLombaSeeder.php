<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Peserta;
use App\Models\KategoriLomba;
use Illuminate\Support\Facades\DB;

class PesertaKategoriLombaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pesertas = Peserta::all();
        $kategoriLombas = KategoriLomba::all();

        // assign 2 kategori acak ke setiap peserta
        foreach ($pesertas as $peserta) {
            $randomKategori = $kategoriLombas->random(2)->pluck('id');
            $peserta->kategoriLomba()->attach($randomKategori);
        }
    }
}
