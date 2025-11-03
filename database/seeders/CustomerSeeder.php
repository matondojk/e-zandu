<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer; 
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {        
        $faker = Faker::create('pt_PT');

        for ($i = 1; $i <= 5; $i++) {
            Customer::create([
                'name' => $faker->firstName . ' ' . $faker->lastName,
                'email' => $faker->unique()->safeEmail(),
                'phone' => '+2449' . rand(20000000, 99999999), // formato de telefone mais realista
                'password' => Hash::make('password'),
                'role' => 'customer', // campo role adicionado
            ]);
        }
    }
}
