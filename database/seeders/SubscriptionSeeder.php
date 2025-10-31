<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subscription::firstOrCreate(
            ['id' => 1],
            [
                'status' => 'active',
                'plan_name' => 'Plano Padrão',
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ]
        );
    }
}
