<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->comment('Ex: Transferência Bancária, Pag. Express, TPA');
            
            // O 'tipo' controla a lógica e os campos a mostrar
            $table->string('type')->comment('bank_transfer, express, cash_on_delivery');
            
            $table->text('instructions')->nullable()->comment('Ex: Envie o comprovativo...');
            
            // Campos para 'bank_transfer'
            $table->string('bank_account_holder')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_iban')->nullable();

            // Campos para 'express'
            $table->string('phone_number')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};