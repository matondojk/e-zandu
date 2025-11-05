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
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province_name')->unique()->comment('Ex: Luanda, Bengo');
            $table->decimal('rate', 10, 2)->default(0)->comment('Valor do frete em Kz');
            $table->string('estimated_delivery_time', 150)->nullable()->comment('Ex: 24-48 horas, 3-5 dias úteis');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};