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
                'faq_who_answer' => 'Eu sou um assistente virtual de IA da E-Zandu, desenvolvido por Matondo João.',
                'faq_time_answer' => 'Para Luanda, a entrega leva em média 24 horas úteis após a confirmação do pagamento.',
                'company_description' => 'Somos a E-Zandu, a sua loja online de confiança para produtos de alta qualidade em Angola.',
                'company_description' => 'Somos a E-Zandu, a sua loja online de confiança para produtos de alta qualidade em Angola.',
                // 'product_types' => ['Roupas', 'Calçados', 'Acessórios'], 

                'nif' => '5000123456',
            ]
        );
    }
}
