<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier; 
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->truncate();
        $faker = Faker::create('pt_PT');

        for ($i = 1; $i <= 10; $i++) {
            Supplier::create([
                'name' => $faker->company . ' Lda.',
                'contact_name' => $faker->name, // 👈 Nome da pessoa de contacto
                'email' => $faker->unique()->safeEmail,
                'phone' => '+2449' . rand(10000000, 99999999),
                'address' => $faker->address,
            ]);
        }
    }
}
