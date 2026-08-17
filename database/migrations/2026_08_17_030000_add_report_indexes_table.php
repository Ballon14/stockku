<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('user_id');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['product_id', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'created_at']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
