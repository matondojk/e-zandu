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
            // Adiciona o novo campo de e-mails de alerta de stock
            $table->string('stock_alert_emails')
                ->nullable() // Permite que seja nulo
                ->after('minimum_stock') // Coloca depois do stock mínimo
                ->comment('E-mails (separados por ;) que recebem alertas de stock baixo.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('stock_alert_emails');
        });
    }
};
