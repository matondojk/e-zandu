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

            // --- CAMPOS REMOVIDOS ---
            // $table->string('document_number')->nullable()->comment('Nº da Fatura/Nota de Compra');
            // $table->date('document_date')->comment('Data de Emissão do Documento');

            // --- NOVOS CAMPOS ADICIONADOS ---
            $table->string('purchase_number')->unique()->nullable()->comment('Número da compra/pedido (manual ou auto)');
            $table->date('issue_date')->nullable()->comment('Data de emissão (criação do pedido)');
            $table->date('expected_delivery_date')->nullable()->comment('Data de entrega prevista');
            $table->string('payment_terms')->nullable()->comment('Condições de pagamento (ex: 30 dias)');
            $table->string('payment_method')->nullable()->comment('Forma de pagamento (ex: Transferência)');
            $table->text('internal_notes')->nullable()->comment('Observações / Notas internas');
            
            // Assumindo que a tabela 'users' usa UUIDs. Se usar bigIncrements, mude para foreignId('user_id')
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null')->comment('Usuário que registrou a compra');
            
            $table->json('attachments')->nullable()->comment('Anexos (fatura, comprovante, etc.)');
            $table->boolean('calculate_iva')->default(true)->comment('Calcular IVA nesta compra?');
            
            // --- CAMPOS MANTIDOS E NECESSÁRIOS (da lógica anterior) ---
            $table->boolean('is_delivered')->default(false)->comment('Material já foi entregue?');
            
            // Status atualizado para os novos valores
            $table->string('status')->default('Aberta')->comment('Aberta, Confirmada, Cancelada, Recebida');

            // --- CAMPOS DE TOTAIS (Calculados via Trigger) ---
            $table->decimal('total_amount', 20, 2)->default(0)->comment('Subtotal (Soma dos itens antes de impostos/descontos)');
            $table->decimal('discount_total', 20, 2)->default(0)->comment('Valor total do desconto');
            $table->decimal('freight_cost', 20, 2)->default(0)->comment('Custo do Frete');
            $table->decimal('iva_amount', 20, 2)->default(0)->comment('Valor do IVA (calculado)');
            $table->decimal('total_payable', 20, 2)->default(0)->comment('Total final a pagar');
            
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->decimal('quantity', 8, 2);
            $table->decimal('unit_cost', 20, 2)->comment('Custo por unidade');
            $table->decimal('discount', 20, 2)->default(0)->nullable();
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
            $table->decimal('unit_cost', 20, 2)->nullable();
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