<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\PurchaseItem; // Certifique-se que você tem o Model PurchaseItem
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PurchaseItemSeeder extends Seeder
{
    public function run(): void
    {
        // ❌ A limpeza (truncate) é feita no DatabaseSeeder.php.
        
        $purchases = Purchase::all();
        $products = Product::all();
        $faker = Faker::create('pt_PT');

        if ($purchases->isEmpty() || $products->isEmpty()) {
            return;
        }

        // 3. Iterar sobre cada Compra (Purchase) e adicionar itens
        foreach ($purchases as $purchase) {
            
            $productsToBuy = $products->random(rand(1, 3));
            
            // Variáveis para recalcular o total da Compra (Purchase)
            $totalPurchaseAmount = 0; // Soma dos (custo * qtd)
            $totalPurchaseIva = 0;    // Soma do IVA de todos os itens
            $totalPurchaseDiscount = 0; // Soma do desconto de todos os itens

            foreach ($productsToBuy as $product) {
                $quantity = rand(5, 50); 
                
                // Custo por unidade (unit_cost)
                $unitCost = $product->price * (rand(55, 75) / 100); 
                
                // Calcular subtotal do item
                $itemSubtotal = $unitCost * $quantity;
                
                // Calcular IVA (ex: 14% sobre o subtotal)
                $ivaAmount = $itemSubtotal * 0.14; 

                // ✅ CAMPOS ADICIONADOS
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => round($unitCost, 2),
                    'discount' => 0.00, // Desconto por item
                    'unit' => $product->unit ?? 'Unidade', // Puxa do produto
                    'notes' => 'Item de compra de teste',
                    'iva_amount' => round($ivaAmount, 2), // IVA calculado
                ]);
                
                // Soma os totais para atualizar a Compra (Purchase)
                $totalPurchaseAmount += ($unitCost * $quantity);
                $totalPurchaseIva += $ivaAmount;
                // $totalPurchaseDiscount += (desconto); // Se 'discount' > 0
            }

            // 4. (RECOMENDADO) Atualizar o total da Compra (Purchase)
            $freight = $purchase->freight_cost ?? 0;
            // $discount = $purchase->discount_total ?? 0; // Usar o totalPurchaseDiscount se preferir

            $purchase->total_amount = round($totalPurchaseAmount, 2); // Subtotal (soma dos itens)
            $purchase->iva_amount = round($totalPurchaseIva, 2);     // IVA (soma dos itens)
            
            // Total a Pagar = (Subtotal + Frete + IVA) - Desconto
            $purchase->total_payable = round(($totalPurchaseAmount + $freight + $totalPurchaseIva) - $purchase->discount_total, 2); 
            $purchase->save();
        }
    }
}