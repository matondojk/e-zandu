<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        // ❌ REMOVIDO: DB::table('cart_items')->truncate();
        // ❌ REMOVIDO: DB::table('carts')->truncate();
        // A limpeza é feita no DatabaseSeeder.php

        $products = Product::all();
        $customers = Customer::all();
        $faker = Faker::create();

        if ($products->isEmpty() || $customers->isEmpty()) return;

        $statuses = ['active', 'paid', 'paid', 'finalized', 'cancelled'];
        $cartsToCreate = 10; 

        for ($i = 0; $i < $cartsToCreate; $i++) {
            $customer = $customers->random();
            $status = $faker->randomElement($statuses);
            
            $cart = Cart::create([
                'user_id' => $customer->id, // Assumindo foreign key customer_id
                'status' => $status,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]);

            $totalPrice = 0;
            $items = $products->random(rand(1, 3)); 

            foreach ($items as $product) {
                $quantity = rand(1, 2);
                $price = $product->promotional_price > 0 ? $product->promotional_price : $product->price;

                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price
                ]);
                $totalPrice += $price * $quantity;
            }

            $cart->total_price = $totalPrice;
            $cart->save();
        }
    }
}
