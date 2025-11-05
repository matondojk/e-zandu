<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cria ou atualiza o utilizador admin
        User::updateOrCreate(
            ['email' => 'demo@e-zandu.com'],
            [
                'name' => 'Demo',
                'role' => 'admin',
                'password' => Hash::make('demo#85'),
                'email_verified_at' => now(),
            ]
        );
    }
}
