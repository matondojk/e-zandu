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
        // Tabela pivot para Muitos-para-Muitos (Produtos <-> Categorias)
        Schema::create('category_product', function (Blueprint $table) {
            // Chave estrangeira para a tabela 'categories'
            $table->foreignUuid('category_id')->constrained()->onDelete('cascade');
            
            // Chave estrangeira para a tabela 'products'
            $table->foreignUuid('product_id')->constrained()->onDelete('cascade');

            // Chave primária composta para garantir que cada par é único
            $table->primary(['category_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
