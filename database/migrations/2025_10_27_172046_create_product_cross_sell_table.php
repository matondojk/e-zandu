<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_cross_sell', function (Blueprint $table) {
            $table->foreignUuid('main_product_id')
                ->constrained('products')
                ->onDelete('cascade');

            $table->foreignUuid('recommended_product_id')
                ->constrained('products')
                ->onDelete('cascade');

            $table->primary(['main_product_id', 'recommended_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cross_sell');
    }
};
