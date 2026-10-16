<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom pembayaran pada tabel purchases
        Schema::table('purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('purchases', 'payment_type')) {
                $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('tanggal');
            }
            if (! Schema::hasColumn('purchases', 'due_date')) {
                $table->date('due_date')->nullable()->after('payment_type');
            }
            if (! Schema::hasColumn('purchases', 'supplier_invoice')) {
                $table->string('supplier_invoice', 100)->nullable()->after('due_date');
            }
            if (! Schema::hasColumn('purchases', 'payment_status')) {
                $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('paid')->after('supplier_invoice');
            }
            if (! Schema::hasColumn('purchases', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status');
            }
        });

        // 2. Buat tabel purchase_payments (riwayat pembayaran tagihan supplier)
        if (! Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->string('payment_number')->unique();
                $table->date('tanggal_bayar');
                $table->decimal('nominal', 15, 2);
                $table->string('metode', 30)->default('transfer');
                $table->string('bank_tujuan')->nullable();
                $table->string('nomor_referensi')->nullable();
                $table->string('bukti_bayar')->nullable();
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['purchase_id', 'supplier_id']);
                $table->index('tanggal_bayar');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');

        Schema::table('purchases', function (Blueprint $table) {
            $columns = [];
            foreach (['payment_type', 'due_date', 'supplier_invoice', 'payment_status', 'paid_amount'] as $col) {
                if (Schema::hasColumn('purchases', $col)) {
                    $columns[] = $col;
                }
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
