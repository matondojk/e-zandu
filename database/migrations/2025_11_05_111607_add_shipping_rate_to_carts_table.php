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
        Schema::table('carts', function (Blueprint $table) {
            $table->foreignUuid('shipping_rate_id')
                ->nullable()
                ->constrained('shipping_rates') // Liga à tabela 'shipping_rates'
                ->onDelete('set null'); // Se a taxa for apagada, o pedido não falha
            
            // 2. O Valor do frete no momento da compra
            $table->decimal('shipping_amount', 10, 2)
                ->default(0)
                ->comment('O valor do frete cobrado neste pedido')
                ->after('shipping_rate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['shipping_rate_id']);
            $table->dropColumn(['shipping_rate_id', 'shipping_amount']);
        });
    }
};
