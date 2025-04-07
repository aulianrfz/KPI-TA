<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Venue;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Venue::insert([
            ['name' => 'Auditorium'],
            ['name' => 'Gedung H Conference Room'],
            ['name' => 'Gedung H Aula Lantai 5'],
            ['name' => 'Gedung H Lantai 3 Ruangan 308'],
            ['name' => 'GH Universal'],
            ['name' => 'Minoru Coffee House'],
        ]);
    }
}
