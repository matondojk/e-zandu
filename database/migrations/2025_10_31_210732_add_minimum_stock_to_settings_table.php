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
        Schema::table('settings', function (Blueprint $table) {
            // Adiciona o novo campo de stock mínimo
            $table->integer('minimum_stock')
                ->default(10) // Define um padrão de 10 unidades
                ->after('shipping_rate') // Coloca depois da taxa de frete
                ->comment('Nível de stock mínimo para disparar alertas.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('minimum_stock');
        });
    }
};
