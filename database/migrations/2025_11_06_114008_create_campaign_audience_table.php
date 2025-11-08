<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela "Pivot" que liga Campanha <-> Cliente (User)
        Schema::create('campaign_audience', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('campaign_id')->constrained('campaigns')->onDelete('cascade');
            
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');

            $table->string('status')->default('pending')->comment('pending, sent, failed');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->unique(['campaign_id', 'user_id']); // Evita duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_audience');
    }
};