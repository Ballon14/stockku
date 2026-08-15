<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('sumber')->nullable()->after('catatan');
            $table->string('offline_id')->nullable()->unique()->after('sumber');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['offline_id']);
            $table->dropColumn(['sumber', 'offline_id']);
        });
    }
};
