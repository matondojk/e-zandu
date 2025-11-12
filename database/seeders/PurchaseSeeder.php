<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = Supplier::all();
        $defaultStock = Stock::where('is_default', true)->first();
        // Pegamos um utilizador aleatório (idealmente admin, mas para seed serve qualquer um)
        $user = User::where('role', 'admin')->first(); 

        if ($suppliers->isEmpty()) {
            $this->command->warn('Nenhum fornecedor encontrado. PurchaseSeeder abortado.');
            return;
        }

        $faker = Faker::create('pt_PT');

        // Criar 10 compras de exemplo
        for ($i = 0; $i < 10; $i++) {
            $supplier = $suppliers->random();
            
            // Gerar datas
            $issueDate = $faker->dateTimeBetween('-2 months', 'now');
            $deliveryDate = (clone $issueDate)->modify('+' . rand(5, 20) . ' days');

            // Definir status e entrega
            $statuses = ['Aberta', 'Confirmada', 'Recebida', 'Cancelada'];
            $status = $faker->randomElement($statuses);
            
            // Se o status for 'Recebida', consideramos entregue
            $isDelivered = ($status === 'Recebida');

            // Gerar custo de frete aleatório (será somado na procedure)
            $freightCost = rand(0, 1) === 1 ? rand(5000, 20000) : 0;

            Purchase::create([
                'supplier_id' => $supplier->id,
                'stock_id' => $defaultStock ? $defaultStock->id : null,
                'user_id' => $user->id, // Novo campo obrigatório

                // Novos Campos
                'purchase_number' => 'PO-' . strtoupper(Str::random(8)), // Ex: PO-X7K9M2P1
                'issue_date' => $issueDate,
                'expected_delivery_date' => $deliveryDate,
                'payment_terms' => $faker->randomElement(['Pronto Pagamento', '30 Dias', '50% Entrada / 50% Entrega']),
                'payment_method' => $faker->randomElement(['Transferência Bancária', 'Pagamento na Entrega']
),
                'internal_notes' => $faker->sentence(),
                'calculate_iva' => $faker->boolean(90), // 90% das compras calculam IVA
                'status' => $status,
                'is_delivered' => $isDelivered,

                // Valores iniciais (serão recalculados pela Procedure após inserir itens)
                'freight_cost' => $freightCost,
                'discount_total' => 0,
                'total_amount' => 0, 
                'iva_amount' => 0,
                'total_payable' => 0, 
            ]);
        }
    }
}