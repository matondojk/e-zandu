<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->comment('O código que o cliente digita (ex: NATAL10)');
            $table->string('type')->comment('fixed (valor fixo) ou percentage (percentagem)');
            $table->decimal('value', 10, 2)->comment('O valor (5000 Kz ou 10%)');
            
            $table->integer('usage_limit')->nullable()->comment('Limite total de utilizações');
            $table->integer('usage_count')->default(0)->comment('Quantas vezes foi usado');
            
            $table->timestamp('expires_at')->nullable()->comment('Data de expiração');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
