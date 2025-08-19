<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Cek jika admin belum ada, buat admin
        if (!User::where('role', 'admin')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'last_name' => 'System',
                'username' => 'admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]);
        }

        // Buat 10 user biasa
        \App\Models\User::factory(10)->create();
    }
}
