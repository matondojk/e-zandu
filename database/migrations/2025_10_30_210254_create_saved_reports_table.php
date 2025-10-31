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
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Relaciona com o Admin (User) que guardou o relatório
            $table->foreignUuid('user_id')
                  ->constrained('users') // Assumindo que a sua tabela de admin é 'users'
                  ->onDelete('cascade');

            $table->string('name')->comment('Ex: Vendas Mensais por Produto');
            $table->text('prompt');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
