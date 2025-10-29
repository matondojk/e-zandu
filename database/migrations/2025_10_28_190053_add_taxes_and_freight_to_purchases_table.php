<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            
            // Adiciona o Custo do Frete (que será inserido manualmente)
            $table->decimal('freight_cost', 10, 2)->default(0);
            
            // Adiciona campos de impostos (IVA 14%)
            $table->decimal('iva_amount', 10, 2)->default(0);
            
            // Adiciona o Total Final (Subtotal + Frete + IVA)
            $table->decimal('total_payable', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['freight_cost', 'iva_amount', 'total_payable']);
        });
    }
};