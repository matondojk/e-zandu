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
            'estimated_delivery_time' => 'Entrega em 24 a 48 horas (Dias úteis)', // Exemplo de texto
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Bengo',
            'rate' => 3500.00,
            'estimated_delivery_time' => '3 a 5 dias úteis', // Exemplo de texto
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Huambo',
            'rate' => 7000.00,
            'estimated_delivery_time' => '7 a 10 dias úteis', // Exemplo de texto
            'is_active' => true,
        ]);
        
        ShippingRate::create([
            'province_name' => 'Benguela',
            'rate' => 7000.00,
            'estimated_delivery_time' => '7 a 10 dias úteis', // Exemplo de texto
            'is_active' => true,
        ]);
    }
}