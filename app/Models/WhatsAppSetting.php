<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WhatsAppSetting extends Model
{
    use HasFactory, HasUuids;

    protected $table='whatsapp_settings';

    protected $fillable = [
        'token',
        'phone_id',
        'verify_token',
        'admin_phone',
    ];

    /**
     * Encripta automaticamente o token ao salvar
     * e desencripta ao ler.
     */
    protected $casts = [
        'token' => 'encrypted',
    ];
}
