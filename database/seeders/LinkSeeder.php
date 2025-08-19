<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('links')->insert([
            // Brand
            ['label' => 'Gatevent.com', 'url' => 'gatevent.com', 'type' => 'brand', 'icon' => null],

            // Logo
            ['label' => 'Logo', 'url' => 'gatevent.com', 'type' => 'logo', 'icon' => null],

            // Footer links
            ['label' => 'About', 'url' => '#', 'type' => 'link', 'icon' => null],
            ['label' => 'How it works', 'url' => '#', 'type' => 'link', 'icon' => null],
            ['label' => 'Careers', 'url' => '#', 'type' => 'link', 'icon' => null],
            ['label' => 'Blog', 'url' => '#', 'type' => 'link', 'icon' => null],
            ['label' => 'Forum', 'url' => '#', 'type' => 'link', 'icon' => null],

            // Socials
            ['label' => 'Twitter', 'url' => 'https://twitter.com', 'type' => 'social', 'icon' => 'https://img.icons8.com/ios-glyphs/24/ffffff/twitter--v1.png'],
            ['label' => 'Instagram', 'url' => 'https://instagram.com', 'type' => 'social', 'icon' => 'https://img.icons8.com/ios-glyphs/24/ffffff/instagram-new.png'],
            ['label' => 'Facebook', 'url' => 'https://facebook.com', 'type' => 'social', 'icon' => 'https://img.icons8.com/ios-glyphs/24/ffffff/facebook-new.png'],
        ]);
    }
}
