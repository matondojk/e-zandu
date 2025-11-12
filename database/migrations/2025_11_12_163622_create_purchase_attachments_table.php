<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_id')->constrained('purchases')->onDelete('cascade');
            
            $table->string('file_path')->comment('Caminho do arquivo no storage');
            $table->string('file_name')->nullable()->comment('Nome original do arquivo');
            $table->string('file_type')->nullable()->comment('Mime type ex: pdf, jpg');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_attachments');
    }
};
