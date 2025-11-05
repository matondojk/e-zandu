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
             // Liga o cupão ao carrinho, se for apagado, o ID fica nulo
            $table->foreignUuid('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');
            
            // Armazena o valor calculado do desconto (ex: 5000 Kz)
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_amount']);
        });
    }
};
