<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_change_logs', function (Blueprint $table) {
            $table->string('tipe_harga', 20)->default('beli')->after('product_id');
            $table->index(['tipe_harga', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('price_change_logs', function (Blueprint $table) {
            $table->dropIndex(['tipe_harga', 'created_at']);
            $table->dropColumn('tipe_harga');
        });
    }
};
