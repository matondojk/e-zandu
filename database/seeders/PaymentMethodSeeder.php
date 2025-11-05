<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::create([
            'name' => 'Transferência Bancária (BAI)',
            'type' => 'bank_transfer',
            'instructions' => 'Transfira o valor total para a conta abaixo e envie o comprovativo para o nosso WhatsApp.',
            'bank_account_holder' => 'E-Zandu (SU), LDA',
            'bank_account_number' => '123456789',
            'bank_iban' => 'AO06.0000.0000.0000.0000.0000.0',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Pagamento Express',
            'type' => 'express',
            'instructions' => 'Pague com Express para o número abaixo e envie o comprovativo para o nosso WhatsApp.',
            'phone_number' => '923123456',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Pagamento na Entrega (Dinheiro)',
            'type' => 'cash_on_delivery',
            'instructions' => 'Você pagará o valor total em dinheiro ao nosso estafeta no momento da entrega.',
            'is_active' => true,
        ]);
    }
}