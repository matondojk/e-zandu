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
        // Tabela de Permissões
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique(); // Slug based on route (ex: admin/vendas)
            $table->string('guard_name')->default('web');
            $table->string('group_name'); // Ex: Vendas, Marketing, Estoque
            $table->timestamps();
        });

        // Tabela de Perfis (Roles)
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique(); // Slug for easy reference (ex: vendedor, financeiro)
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        // Tabela Pivot: user_has_roles (Um usuário pode ter N Perfis)
        Schema::create('user_has_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });

        // Tabela Pivot: role_has_permissions (Um Perfil pode ter N Permissões)
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');

            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('user_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};