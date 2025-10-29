<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stocks')->truncate();

        $stores = [
            ['name' => 'Armazém Central (Default)', 'is_default' => true],
            ['name' => 'Loja Luanda Sul', 'is_default' => false],
            ['name' => 'Loja Benguela', 'is_default' => false],
            ['name' => 'Armazém Secundário', 'is_default' => false],
            ['name' => 'Loja Huambo', 'is_default' => false],
        ];

        foreach ($stores as $store) {
            Stock::create($store);
        }
    }
}