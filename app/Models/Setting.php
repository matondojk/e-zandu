<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Setting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        // ================================
        // Configurações Gerais
        // ================================
        'store_name',

        // ================================
        // Contatos (sem admin_phone e sem admin_whatsapp)
        // ================================
        'support_phone',
        'company_email',
        'support_email',
        'company_address',
        'company_city',
        'company_country',
        'working_hours',

        // Redes Sociais
        'facebook_url',
        'instagram_url',
        'whatsapp_url',

        // ================================
        // Logos e Ícones
        // ================================
        'logo_light_path',
        'logo_dark_path',
        'icon_path',

        // ================================
        // Pagamento
        // ================================
        'enable_bank_transfer',
        'bank_account_holder',
        'bank_account_number',
        'bank_iban',

        'enable_express_payment',
        'express_payment_phone',

        'enable_cash_on_delivery',

        // ================================
        // IA (FAQs)
        // ================================
        'faq_who_answer',
        'faq_locations_answer',
        'faq_time_answer',
        'faq_payment_answer',

        // ================================
        // Empresa
        // ================================
        'nif',

        // Caso ainda use logo_path em algum lugar:
        'logo_path',
    ];

    protected $casts = [
        'enable_bank_transfer' => 'boolean',
        'enable_express_payment' => 'boolean',
        'enable_cash_on_delivery' => 'boolean',
    ];
}
