<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::create([
            'code' => 'NATAL10',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'expires_at' => now()->addMonths(1),
        ]);

        Coupon::create([
            'code' => 'E-ZANDU5K',
            'type' => 'fixed',
            'value' => 5000,
            'usage_limit' => 100,
            'is_active' => true,
        ]);
        
        Coupon::create([
            'code' => 'EXPIRADO',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => false,
            'expires_at' => now()->subDay(),
        ]);
    }
}