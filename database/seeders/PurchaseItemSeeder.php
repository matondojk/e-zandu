<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\PurchaseItem; // Certifique-se que você tem o Model PurchaseItem
use Illuminate\Support\Facades\DB; // 👈 IMPORTANTE: Para chamar a Procedure
use Faker\Factory as Faker;

class PurchaseItemSeeder extends Seeder
{
    public function run(): void
    {
        $purchases = Purchase::all();
        $products = Product::all();
        $faker = Faker::create('pt_PT');

        if ($purchases->isEmpty() || $products->isEmpty()) {
            $this->command->info('Nenhuma compra ou produto encontrado. Seeder de Itens de Compra ignorado.');
            return;
        }

        // 3. Iterar sobre cada Compra (Purchase) e adicionar itens
        foreach ($purchases as $purchase) {
            
            $productsToBuy = $products->random(rand(1, 3));

            // ✅ REMOVIDO: Variáveis de total. A Procedure trata disso.
            // $totalPurchaseAmount = 0;
            // $totalPurchaseIva = 0;
            // $totalPurchaseDiscount = 0;

            foreach ($productsToBuy as $product) {
                $quantity = rand(5, 50); 
                
                // Custo por unidade (unit_cost)
                $unitCost = $product->price * (rand(55, 75) / 100); 
                
                // ✅ REMOVIDO: Cálculo de subtotal e IVA do item aqui.

                // ✅ CORRIGIDO: 'iva_amount' foi removido do create()
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => round($unitCost, 2),
                    'discount' => 0.00, // Desconto por item
                    'unit' => $product->unit ?? 'Unidade', // Puxa do produto
                    'notes' => 'Item de compra de teste',
                    // 'iva_amount' => ... (REMOVIDO)
                ]);
                
                // ✅ REMOVIDO: Soma manual dos totais
            }

            // 4. ✅ GRANDE MUDANÇA: Chamar a Stored Procedure
            // Em vez de calcular manualmente no PHP, mandamos a BD recalcular tudo.
            // A procedure vai buscar todos os itens que acabámos de criar,
            // calcula o subtotal, descontos, o IVA de 14% sobre o líquido
            // e atualiza a 'purchases' automaticamente.
            try {
                DB::statement('CALL sp_update_purchase_data(?)', [$purchase->id]);
            } catch (\Exception $e) {
                $this->command->error("Erro ao chamar sp_update_purchase_data para Compra ID {$purchase->id}: " . $e->getMessage());
            }

            // ✅ REMOVIDO: Toda a lógica de $purchase->save()
        }
    }
}