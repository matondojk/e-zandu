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
            $table->string('admin_phone')->nullable();
            $table->string('support_email')->nullable();

            // Configurações de Pagamento
            $table->string('iban')->nullable();
            $table->string('multicaixa_express')->nullable();

            // Configurações da IA (FAQs)
            $table->text('faq_who_answer')->nullable();
            $table->text('faq_locations_answer')->nullable();
            $table->text('faq_time_answer')->nullable();
            $table->text('faq_payment_answer')->nullable();

            $table->string('nif')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('icon_path')->nullable();
            $table->decimal('shipping_rate', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
