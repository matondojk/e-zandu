<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingRate;

class ShippingRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingRate::create([
            'province_name' => 'Luanda',
            'rate' => 2000.00,
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Bengo',
            'rate' => 3500.00,
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Huambo',
            'rate' => 7000.00,
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Benguela',
            'rate' => 7000.00,
            'is_active' => true,
        ]);
    }
}