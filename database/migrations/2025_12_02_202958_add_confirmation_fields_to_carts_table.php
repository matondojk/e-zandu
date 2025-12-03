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
            $table->uuid('payment_confirmed_by')->nullable();
            $table->uuid('delivery_confirmed_by')->nullable();
            $table->uuid('rejected_by')->nullable();

            // Relacionamentos
            $table->foreign('payment_confirmed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('delivery_confirmed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();

            // Campos de auditoria
            $table->timestamp('payment_confirmed_at')->nullable();
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            // Remove as chaves estrangeiras primeiro
            $table->dropConstrainedForeignId('payment_confirmed_by');
            $table->dropConstrainedForeignId('delivery_confirmed_by');
            $table->dropConstrainedForeignId('rejected_by');
            
            // Remove as colunas de timestamp
            $table->dropColumn('payment_confirmed_at');
            $table->dropColumn('delivery_confirmed_at');
            $table->dropColumn('rejected_at');
        });
    }
};
