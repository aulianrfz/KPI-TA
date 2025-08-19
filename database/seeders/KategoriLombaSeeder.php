<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriLomba;

class KategoriLombaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriLomba = [
            ['name' => 'Creative Dance', 'durasi' => 60],
            ['name' => 'Tourism Ideathon', 'durasi' => 90],
            ['name' => 'Bidding Event Proposal', 'durasi' => 45],
            ['name' => 'Making Bed', 'durasi' => 30],
            ['name' => 'Tourism Speech Competition', 'durasi' => 60],
            ['name' => 'Manual Brew', 'durasi' => 40],
            ['name' => 'Black Box Cooking Battle', 'durasi' => 120],
        ];

        foreach ($kategoriLomba as $data) {
            KategoriLomba::create($data);
        }
    }
}
