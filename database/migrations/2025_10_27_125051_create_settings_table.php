<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Configurações Gerais
            $table->string('store_name')->nullable();

            // Contatos
            $table->string('support_phone')->nullable();        // Linha de suporte
            $table->string('company_email')->nullable();        // Email geral da empresa
            $table->string('support_email')->nullable();        // Email de suporte
            $table->string('company_address')->nullable();      // Endereço físico
            $table->string('company_city')->nullable();         // Cidade
            $table->string('company_country')->nullable();      // País
            $table->string('working_hours')->nullable();        // Ex: Seg-Sab 08h-18h

            // Redes Sociais
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('whatsapp_url')->nullable();

            // Informações da IA (FAQs)
            $table->text('faq_who_answer')->nullable();
            $table->text('faq_time_answer')->nullable();

            // Empresa
            $table->string('nif')->nullable();

            // Logos e ícones
            $table->string('logo_light_path')->nullable();
            $table->string('logo_dark_path')->nullable();
            $table->string('icon_path')->nullable();

            // Configuração de envio
            $table->decimal('shipping_rate', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
