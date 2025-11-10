<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('nif')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('location')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignUuid('stock_id')->constrained('stocks')->onDelete('restrict');
            $table->string('document_number')->nullable()->comment('Nº da Fatura/Nota de Compra');
            $table->date('document_date')->comment('Data de Emissão do Documento');
            $table->boolean('is_delivered')->default(false)->comment('Material já foi entregue?');
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            // ✅ Campos que faltavam (do Seeder) adicionados
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // ✅ CORRIGIDO (estava comentado/errado)
            $table->foreignUuid('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');

            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_cost', 10, 2)->comment('Custo por unidade');
            $table->decimal('discount', 8, 2)->default(0)->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_id')->constrained('stocks')->onDelete('restrict');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->string('type')->comment('in_purchase, out_sale, adjustment');
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_cost', 10, 2)->nullable();

            // $table->uuidMorphs('reference'); 
            $table->uuid('reference_id')->nullable();
            $table->string('reference_type')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('suppliers');
    }
};
