<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // --- 1. Detalhes ---
            $table->string('name')->comment('Nome interno, Ex: Promo de Natal 10%');
            $table->string('channel')->comment('whatsapp, email, or both');
            $table->timestamp('scheduled_at')
                ->useCurrent()
                ->comment('Agendar o envio para esta data');
            $table->string('status')->default('draft')->comment('draft, scheduled, processing, sent, failed');

            // --- 2. Conteúdo ---
            $table->string('content_type')->default('message')->comment('message, coupon, product_promo');
            $table->foreignUuid('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');

            // --- 3. Templates de Mensagem (Gerados pela IA) ---
            $table->text('whatsapp_template')->nullable();
            $table->string('email_subject')->nullable();
            $table->text('email_template')->nullable();

            // --- 4. Audiência (Onde guardamos o prompt da IA) ---
            $table->string('audience_type')->default('manual'); // manual, ai_prompt, all
            $table->text('audience_prompt')->nullable()->comment('O prompt do admin. Ex: clientes de Luanda');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
