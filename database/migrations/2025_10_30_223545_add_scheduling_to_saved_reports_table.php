<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->boolean('is_scheduled')->default(false)->after('prompt');
            $table->string('frequency')->nullable()->after('is_scheduled')->comment('ex: daily, weekly, monthly');
            $table->string('recipient_email')->nullable()->after('frequency');
        });
    }

    public function down(): void
    {
        Schema::table('saved_reports', function (Blueprint $table) {
            $table->dropColumn(['is_scheduled', 'frequency', 'recipient_email']);
        });
    }
};