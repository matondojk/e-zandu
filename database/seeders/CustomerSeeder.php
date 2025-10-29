<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // ❌ REMOVA: DB::table('customers')->truncate();
        // A limpeza é feita no DatabaseSeeder.php
        
        $faker = Faker::create('pt_PT');

        for ($i = 1; $i <= 5; $i++) {
            Customer::create([
                'name' => $faker->firstName . ' ' . $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'phone' => '+24492' . rand(0000000, 9999999),
                'password' => Hash::make('password'),
            ]);
        }
    }
}