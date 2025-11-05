<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        // Usar updateOrCreate para garantir que apenas a linha ID=1 exista
        Setting::updateOrCreate(
            ['id' => '00000000-0000-0000-0000-000000000001'],
            [
                'store_name' => 'E-Zandu Loja',
                'admin_phone' => '9XXXXXXXX',
                'support_email' => 'suporte@e-zandu.com',

                
                'faq_who_answer' => 'Somos a E-Zandu, a sua loja online de confiança.',
                'faq_locations_answer' => 'Entregamos em todo o país.',
                'faq_time_answer' => 'O nosso horário de atendimento é das 8h às 18h.',
                'faq_payment_answer' => 'Aceitamos Transferência Bancária, Pagamento Express e Pagamento na Entrega.',

                'nif' => '5000123456',
            ]
        );
    }
}