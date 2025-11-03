<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Setting extends Model
{
    use HasFactory, HasUuids;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'id',
        'store_name',
        'minimum_stock',
        'stock_alert_emails',
        'admin_phone',
        'support_email',
        'iban',
        'multicaixa_express',
        'faq_who_answer',
        'faq_locations_answer',
        'faq_time_answer',
        'faq_payment_answer',
        'company_description',
        'product_types',
        'nif',
        'logo_path',
        'icon_path',
        'shipping_rate'
    ];

    protected $casts = [
        'product_types' => 'array', // <-- NOVO: Trata o JSON como array
    ];
}
