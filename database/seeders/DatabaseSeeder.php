<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder untuk pengaturan default
        $this->call(SettingSeeder::class);

        // [TAMBAHKAN BLOK INI]
        // Cari atau buat pengguna Admin utama
        User::firstOrCreate(
            ['email' => 'admin@example.com'], // Kunci unik untuk mencari
            [
                'name' => 'Admin Utama',
                'password' => Hash::make('admin123'), // Ganti 'password' dengan password yang aman
                'role' => 'admin',
                'email_verified_at' => now(), // Langsung verifikasi email
            ]
        );
    }
}