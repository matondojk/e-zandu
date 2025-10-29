<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ CORRIGIDO: Linha 15 (DB::table('purchases')->truncate()) FOI REMOVIDA.

        $suppliers = Supplier::all();

        if ($suppliers->isEmpty()) return;

        $faker = Faker::create();

        for ($i = 0; $i < 5; $i++) {
            $supplier = $suppliers->random();
            
            // Gerar valores de custo
            $totalAmount = rand(50000, 500000);
            $freightCost = $totalAmount * 0.05; // 5% de frete
            $ivaAmount = $totalAmount * 0.14; // 14% de IVA
            $totalPayable = $totalAmount + $freightCost + $ivaAmount;

            Purchase::create([
                'supplier_id' => $supplier->id,
                'stock_id' => \App\Models\Stock::where('is_default', true)->first()->id, // Armazém Default
                'document_number' => 'NF' . rand(100000, 999999), // Nº da Fatura/Nota de Compra
                'document_date' => now()->subDays(rand(10, 60)),
                'is_delivered' => $faker->boolean(80), // 80% de chance de já ter sido entregue
                'discount_total' => 0.00,
                'total_amount' => $totalAmount, // Valor sem frete e IVA
                'freight_cost' => round($freightCost, 2),
                'iva_amount' => round($ivaAmount, 2),
                'total_payable' => round($totalPayable, 2), // Total a pagar (com frete e IVA)
            ]);
        }
    }
}
