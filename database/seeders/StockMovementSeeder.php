<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Purchase;
use App\Models\PurchaseItem; // Necessário para obter o custo unitário
use Illuminate\Support\Facades\DB;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        // ❌ REMOVIDO: DB::table('stock_movements')->truncate(); (Feito no DatabaseSeeder)

        $products = Product::all();
        $defaultStock = Stock::where('is_default', true)->first();
        $purchaseItems = PurchaseItem::with('product')->get(); // Carrega o produto para o custo

        if ($products->isEmpty() || !$defaultStock) return;

        // 1. Entrada de estoque (Baseado nos Itens de Compra)
        if ($purchaseItems->isNotEmpty()) {
            foreach ($purchaseItems as $item) {
                StockMovement::create([
                    'stock_id' => $defaultStock->id,
                    'product_id' => $item->product_id,
                    'type' => 'in_purchase', // ✅ Tipo: Entrada por Compra
                    'quantity' => $item->quantity, // Quantidade positiva
                    'unit_cost' => $item->cost_price, // ✅ Custo unitário da compra
                    'reference_type' => 'teste', // ✅ Modelo de Referência
                    'reference_id' => $item->purchase_id, // ✅ ID da Compra
                    'notes' => 'Entrada de estoque via Compra #' . $item->purchase_id
                ]);
            }
        }

        // 2. Simulação de Saída (Vendas)
        $productsToSell = $products->random(min(5, $products->count()));
        
        foreach ($productsToSell as $product) {
            // Verifica o estoque atual (soma de todas as movimentações)
            $currentStock = StockMovement::where('product_id', $product->id)
                                         ->where('stock_id', $defaultStock->id)
                                         ->sum('quantity');
                                         
            $quantityToSell = rand(1, min(5, $currentStock)); // Vende no máximo 5, e não mais do que tem
            
            if ($quantityToSell > 0) {
                 StockMovement::create([
                    'stock_id' => $defaultStock->id,
                    'product_id' => $product->id,
                    'type' => 'out_sale', // ✅ Tipo: Saída por Venda
                    'quantity' => -$quantityToSell, // Quantidade negativa
                    'unit_cost' => $product->price, // Custo da saída (preço de venda)
                    'reference_type' => 'teste', // (Opcional: Pode ligar ao Cart/Order se quiser)
                    'reference_id' => $product->id,
                    'notes' => 'Simulação de vendas para teste de fluxo'
                ]);
            }
        }
    }
}

