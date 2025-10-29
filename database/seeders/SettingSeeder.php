<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting; // Importar o Model

class SettingSeeder extends Seeder
{
    /**
     * Executa o seeder para criar o registo de configurações padrão.
     */
    public function run(): void
    {
        // Usar updateOrCreate para criar se não existir, ou atualizar se já existir
        Setting::updateOrCreate(
            ['id' => 1], // Chave única para encontrar
            [
                // Valores Padrão (serão substituídos pelos `defaults` da migração se for a 1ª vez)
                'store_name' => 'TangaWA Shop',
                'nif' => '5000000000', // <-- NOVO: NIF Adicionado
                'shipping_rate' => 1000.00, // <-- NOVO: Taxa de Frete (1000 Kz)
                'admin_phone' => '244932932532',
                'support_email' => 'admin@tangawa.com',
                'iban' => 'AO06...',
                'multicaixa_express' => '9XX XXX XXX',
                'faq_who_answer' => 'Eu sou um assistente virtual de IA da TangaWA Shop, desenvolvido por Matondo João.',
                'faq_locations_answer' => 'De momento, entregamos apenas na província de Luanda.',
                'faq_time_answer' => 'Para Luanda, a entrega leva em média 24 horas úteis após a confirmação do pagamento.',
                'faq_payment_answer' => 'Aceitamos pagamento por Multicaixa Express ou Transferência Bancária (IBAN). Ao finalizar o pedido, você receberá um link com todas as instruções.',
                'company_description' => 'Somos a TangaWA Shop, a sua loja online de confiança para produtos de alta qualidade em Angola.',
                'product_types' => ['Roupas', 'Calçados', 'Acessórios'], // O Model vai converter isto para JSON
            ]
        );
    }
}
