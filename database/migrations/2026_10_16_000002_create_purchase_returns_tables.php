<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah returned_qty ke purchase_items
        if (! Schema::hasColumn('purchase_items', 'returned_qty')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->unsignedInteger('returned_qty')->default(0)->after('qty');
            });
        }

        // 2. Perbarui enum status pada purchases agar mendukung partial_return dan returned
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('status', ['pending', 'received', 'cancelled', 'partial_return', 'returned'])->default('received')->change();
        });

        // 3. Buat tabel purchase_returns
        if (! Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->string('return_number')->unique();
                $table->date('tanggal');
                $table->decimal('total_nilai', 15, 2)->default(0);
                $table->text('alasan')->nullable();
                $table->string('status', 30)->default('completed');
                $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['purchase_id', 'supplier_id']);
                $table->index('tanggal');
            });
        }

        // 3. Buat tabel purchase_return_items
        if (! Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_item_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->integer('qty');
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');

        if (Schema::hasColumn('purchase_items', 'returned_qty')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn('returned_qty');
            });
        }
    }
};
