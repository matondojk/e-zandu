<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem; // Importar
use App\Models\Product;
use App\Models\Customer; // Importar
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Encontra ou cria um carrinho ATIVO para um Cliente.
     */
    public function getActiveCart(Customer $customer): Cart
    {
        // Agora usa customer_id em vez de user_phone
        return Cart::firstOrCreate(
            ['user_id' => $customer->id, 'status' => 'active'],
            ['user_phone' => $customer->phone] // Mantém o user_phone para referência
        );
    }

    /**
     * Adiciona um item ao carrinho, com lógica de preço promocional.
     *
     * ✅ CORREÇÃO:
     * O ID do Produto ($productId) é um UUID (string), não um INT.
     * Trocamos o type-hint de 'int' para 'string'.
     */
    public function addItemToCart(Cart $cart, string $productId, int $quantity = 1): Product
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new Exception("Produto não encontrado.");
        }

        // ✅ Pedido 4: Lógica de Preço Promocional
        $priceToAdd = ($product->promotional_price > 0)
            ? $product->promotional_price
            : $product->price;

        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->price = $priceToAdd; // Garante que o preço é atualizado se mudou
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $priceToAdd // ✅ Usar o preço correto
            ]);
        }

        $this->recalculateCartTotal($cart);
        return $product;
    }

    /**
     * Recalcula o total do carrinho.
     */
    public function recalculateCartTotal(Cart $cart)
    {
        // Atualiza o total_price no próprio carrinho
        $total = $cart->items()->sum(DB::raw('price * quantity'));
        $cart->total_price = $total;
        $cart->save();
    }

    /**
     * Formata o preço para Kz.
     */
    public function formatPrice($price): string
    {
        // Mantive o seu formato Kz com 2 casas decimais
        return number_format($price, 2, ',', '.') . ' Kz';
    }
}

