<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PurchaseItemSeeder extends Seeder
{
    public function run(): void
    {
        $purchases = Purchase::all();
        $products = Product::all();

        if ($purchases->isEmpty() || $products->isEmpty()) {
            $this->command->info('Nenhuma compra ou produto encontrado. Seeder de Itens de Compra ignorado.');
            return;
        }

        foreach ($purchases as $purchase) {
            // Escolher entre 2 a 5 produtos aleatórios para esta compra
            $productsToBuy = $products->random(rand(2, 5));

            foreach ($productsToBuy as $product) {
                
                // ✅ CORREÇÃO: Reduzimos a quantidade para evitar estourar o limite do banco
                // Antes estava rand(10, 100). Agora vamos colocar rand(1, 10).
                // Se o produto custa 2 milhões, 10 unidades = 20 milhões (cabe no banco).
                // 100 unidades = 200 milhões (estoura o banco se for DECIMAL 10,2).
                $quantity = rand(1, 10); 

                // LÓGICA DE PREÇO: Custo entre 5% a 10% ABAIXO do preço de venda.
                $percent = rand(50, 60) / 100;
                $unitCost = $product->price * $percent;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => round($unitCost, 2),
                    'discount' => 0.00,
                    'unit' => $product->unit ?? 'Unidade',
                    'notes' => 'Item gerado via Seeder',
                ]);
            }

            // Chamar a Stored Procedure para recalcular os totais
            try {
                DB::statement('CALL sp_update_purchase_data(?)', [$purchase->id]);
            } catch (\Exception $e) {
                $this->command->error("Erro ao atualizar totais da Compra ID {$purchase->id}: " . $e->getMessage());
            }
        }
    }
}