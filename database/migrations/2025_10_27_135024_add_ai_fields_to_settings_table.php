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
            // Informações gerais da empresa para a IA
            $table->text('company_description')->nullable()->after('support_email');

            // Tipos de produtos (guardado como JSON array)
            $table->json('product_types')->nullable()->after('company_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['company_description', 'product_types']);
        });
    }
};
