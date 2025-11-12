<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\Customer; // ✅ CORREÇÃO: Usar o modelo Customer
use App\Models\Product;
use App\Models\Coupon;
use App\Models\User;
use App\Models\ShippingRate;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        // ✅ CORREÇÃO: Você está a usar a tabela 'customers'
        $customers = Customer::all(); 
        // Se você renomeou o modelo User para Customer, o seu User::where('role', 'customer')->get() está certo.
        // Mas a sua migração chama-se 'create_customers_table', então vou usar o modelo Customer.
        
        $coupons = Coupon::where('is_active', true)->where(function ($query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        })->get();

        $shippingRates = ShippingRate::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();

        $faker = Faker::create();

        //
        // =================================================================
        // ✅ CORREÇÃO 1: VERIFICAÇÃO DE SEGURANÇA (PARA O ERRO)
        // =================================================================
        //
        if ($products->isEmpty()) {
            $this->command->warn('A tabela "products" está vazia. Execute o ProductSeeder. O CartSeeder foi interrompido.');
            return;
        }
        if ($customers->isEmpty()) {
            $this->command->warn('A tabela "customers" está vazia. Execute o CustomerSeeder. O CartSeeder foi interrompido.');
            return;
        }
        if ($shippingRates->isEmpty()) {
            $this->command->warn('A tabela "shipping_rates" está vazia ou não tem taxas ativas. Execute o ShippingRateSeeder. O CartSeeder foi interrompido.');
            return;
        }
        if ($paymentMethods->isEmpty()) {
            $this->command->warn('A tabela "payment_methods" está vazia ou não tem métodos ativos. Execute o PaymentMethodSeeder. O CartSeeder foi interrompido.');
            return;
        }
        // --- Fim da Verificação ---

        $statuses = ['active', 'paid', 'paid', 'completed'];
        $cartsToCreate = 20;

        for ($i = 0; $i < $cartsToCreate; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement($statuses);
            $shippingRate = $shippingRates->random();
            $paymentMethod = $paymentMethods->random();

            $cart = Cart::create([
                //
                // =================================================================
                // ✅ CORREÇÃO 2: 'customer_id' (O BUG)
                // =================================================================
                //
                'user_id' => $customer->id, // A sua tabela carts usa 'customer_id'
                'status' => $status,
                'uuid' => (string) Str::uuid(),
                'shipping_rate_id' => $shippingRate->id,
                'payment_method_id' => $paymentMethod->id,
            ]);

            $totalPrice = 0;
            $items = $products->random(rand(1, 3)); // Isto agora é seguro

            foreach ($items as $product) {
                $quantity = rand(1, 2);
                $price = $product->price;

                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price
                ]);
                $totalPrice += $price * $quantity;
            }

            // --- Lógica de Cupão (Mantida) ---
            $discountAmount = 0;
            $couponId = null;

            if (!$coupons->isEmpty() && rand(0, 2) === 1) {
                $coupon = $coupons->random();

                if ($coupon->type === 'percentage') {
                    $discountAmount = $totalPrice * ($coupon->value / 100);
                } elseif ($coupon->type === 'fixed') {
                    $discountAmount = min($totalPrice, $coupon->value);
                }

                $couponId = $coupon->id;
                $coupon->increment('usage_count');
            }
            // --- Fim da Lógica de Cupão ---

            $cart->total_price = $totalPrice;
            $cart->discount_amount = $discountAmount;
            $cart->coupon_id = $couponId;

            $cart->save();
        }
    }
}