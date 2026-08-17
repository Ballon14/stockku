<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('harga_beli', 15, 2)->nullable()->after('harga');
        });

        // Snapshot harga beli untuk data historis dari harga beli produk saat ini
        DB::table('sale_items')
            ->whereNull('harga_beli')
            ->select('product_id')
            ->distinct()
            ->pluck('product_id')
            ->each(function (int $productId) {
                $hargaBeli = DB::table('products')->where('id', $productId)->value('harga_beli');
                DB::table('sale_items')
                    ->where('product_id', $productId)
                    ->whereNull('harga_beli')
                    ->update(['harga_beli' => $hargaBeli]);
            });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('harga_beli', 15, 2)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('harga_beli');
        });
    }
};
