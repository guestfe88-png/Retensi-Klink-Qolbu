<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'nama_lengkap' => 'Administrator',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'petugas'],
            [
                'email' => 'petugas@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('petugas123'),
                'nama_lengkap' => 'Petugas Arsip',
                'role' => 'petugas',
            ]
        );
    }
}
