<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Peserta;

class PesertaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Peserta::insert([
            ['name' => 'Peserta 1'],
            ['name' => 'Peserta 2'],
            ['name' => 'Peserta 3'],
            ['name' => 'Peserta 4'],
        ]);
    }
}
