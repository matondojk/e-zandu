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
                'store_name'        => 'E-Zandu',

                'support_phone'     => '923111222',
                'support_email'     => 'suporte@e-zandu.com',
                'company_email'     => 'contacto@e-zandu.com',
                'company_address'   => 'Luanda, Angola',
                'company_city'      => 'Luanda',
                'company_country'   => 'Angola',

                'working_hours'     => 'Seg–Sab: 08h00–18h00',

                'facebook_url'      => 'https://facebook.com/ezandu',
                'instagram_url'     => 'https://instagram.com/ezandu',
                'whatsapp_url'      => 'https://wa.me/244923111222',

                'faq_who_answer'    => 'Eu sou um assistente virtual de IA da E-Zandu, desenvolvido por Matondo João.',
                'faq_time_answer'   => 'Para Luanda, a entrega leva em média 24 horas úteis após a confirmação do pagamento.',

                'nif'               => '5000123456',

                'logo_light_path'   => 'https://e-zandu.com/default/logo-light.png',
                'logo_dark_path'    => 'https://e-zandu.com/default/logo-dark.png',
                'icon_path'         => 'https://e-zandu.com/default/apple-touch-icon.png',

                'shipping_rate'     => 0,
            ]
        );
    }
}
