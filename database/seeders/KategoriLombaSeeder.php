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
    public function run()
    {
        KategoriLomba::insert([
            ['name' => 'Creative Dance', 'duration' => 5],
            ['name' => 'Bidding Event Proposal', 'duration' => 30],
            ['name' => 'Tourism Ideathon', 'duration' => 5],
            ['name' => 'Tourism Speech Competition', 'duration' => 7],
            ['name' => 'Black Box Cooking Battle', 'duration' => 15],
            ['name' => 'Making Bed', 'duration' => 15],
            ['name' => 'Manual Brew', 'duration' => 20],
        ]);
    }
}
