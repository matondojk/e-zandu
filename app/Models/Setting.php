<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Setting extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        // Configurações Gerais
        'store_name',
        'admin_phone',
        'support_email',

        // ✅ NOVOS CAMPOS DE PAGAMENTO
        'enable_bank_transfer',
        'bank_account_holder',
        'bank_account_number',
        'bank_iban',
        'enable_express_payment',
        'express_payment_phone',
        'enable_cash_on_delivery',

        // Configurações da IA (FAQs)
        'faq_who_answer',
        'faq_locations_answer',
        'faq_time_answer',
        'faq_payment_answer',

        // Outros
        'nif',
        'logo_path',
        'icon_path',
    ];

    protected $casts = [
        'enable_bank_transfer' => 'boolean',
        'enable_express_payment' => 'boolean',
        'enable_cash_on_delivery' => 'boolean',
    ];
}