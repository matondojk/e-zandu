<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('version'); // Ex: 1.0.1
            $table->string('title'); // Ex: Correções de Faturação
            $table->json('features'); // Onde guardamos a descrição e os planos
            $table->string('status')->default('launched'); // Já vem "lançado" do SGS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }
};
