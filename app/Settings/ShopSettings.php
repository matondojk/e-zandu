<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShopSettings extends Settings
{
    // --- Configurações Gerais ---
    public string $store_name;
    public string $admin_phone;
    public string $support_email;

    // --- Configurações de Pagamento ---
    public string $iban;
    public string $multicaixa_express;

    // --- Configurações da IA (FAQs) ---
    public string $faq_who_answer;
    public string $faq_locations_answer;
    public string $faq_time_answer;
    public string $faq_payment_answer;

    public static function group(): string
    {
        return 'shop';
    }

    public static function defaults(): array
    {
        return [
            'store_name' => 'TangaWA Shop',
            'admin_phone' => '244932932532',
            'support_email' => 'matondojoaokitemoco@gmail.com',
            'iban' => 'AO06004400001234567890184',
            'multicaixa_express' => '9XX XXX XXX',
            'faq_who_answer' => 'Eu sou um assistente virtual de IA da TangaWA Shop, desenvolvido por Matondo João.',
            'faq_locations_answer' => 'De momento, entregamos apenas na província de Luanda.',
            'faq_time_answer' => 'Para Luanda, a entrega leva em média 24 horas úteis após a confirmação do pagamento.',
            'faq_payment_answer' => 'Aceitamos pagamento por Multicaixa Express ou Transferência Bancária (IBAN). Ao finalizar o pedido, você receberá um link com todas as instruções.',
        ];
    }
}
