<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('token')->comment('Token da API do WhatsApp');
            $table->string('phone_id')->comment('ID do Número de Telefone');
            $table->string('verify_token')->comment('Token de Verificação do Webhook');
            $table->string('admin_phone')->comment('Número do admin para receber alertas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_settings');
    }
};