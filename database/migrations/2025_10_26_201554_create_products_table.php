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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->text('short_description');
            $table->decimal('price');
            
            $table->string('image_url');
            $table->string('slug')->unique();

            // Campos de Identificação/Gestão
            $table->string('sku')->nullable()->unique();
            $table->string('unit')->nullable();

            // Atributos Técnicos/Físicos
            $table->string('drawing')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('volume')->nullable();
            $table->string('dimension')->nullable();

            // Variações Padrão (Usado para exibir opções e no futuro como base para estoque complexo)
            $table->json('colors')->nullable();
            $table->json('sizes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
