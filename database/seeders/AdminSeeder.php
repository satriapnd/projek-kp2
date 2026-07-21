<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin Utama
        User::firstOrCreate(
            ['email' => 'admin@bukutamu.id'],
            [
                'name' => 'Admin Utama',
                'email' => 'admin@bukutamu.id',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Akun Petugas (opsional, bisa dihapus jika hanya butuh 1 akun)
        User::firstOrCreate(
            ['email' => 'petugas@bukutamu.id'],
            [
                'name' => 'Petugas Jaga',
                'email' => 'petugas@bukutamu.id',
                'password' => Hash::make('petugas123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
