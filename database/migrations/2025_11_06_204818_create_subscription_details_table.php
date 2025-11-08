<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta tabela só deve ter UMA linha, contendo o estado atual do tenant
        Schema::create('subscription_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // ID da assinatura no seu gestor_central
            $table->uuid('central_subscription_id')->unique(); 
            
            // O Identificador (essencial, pro)
            $table->string('plan_identifier'); 
            
            // O nome (Plano PRO)
            $table->string('plan_name'); 
            
            // O status (active, canceled, paused)
            $table->string('status'); 
            
            // Data de expiração
            $table->timestamp('ends_at')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_details');
    }
};