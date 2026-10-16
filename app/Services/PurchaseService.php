<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        protected StockService $stockService,
        protected PriceChangeService $priceChangeService,
    ) {}

    public function createPurchase(
        int $supplierId,
        string $tanggal,
        array $items,
        ?string $keterangan = null,
        $fotoNota = null,
        string $paymentType = 'cash',
        ?string $dueDate = null,
        ?string $supplierInvoice = null
    ): Purchase {
        return DB::transaction(function () use ($supplierId, $tanggal, $items, $keterangan, $fotoNota, $paymentType, $dueDate, $supplierInvoice) {
            $total = 0;

            foreach ($items as $key => $item) {
                $items[$key]['subtotal'] = $item['harga'] * $item['qty'];
                $total += $items[$key]['subtotal'];
            }

            // Store foto nota if uploaded
            $fotoNotaPath = null;
            if ($fotoNota) {
                $fotoNotaPath = $fotoNota->store('purchases/nota', 'public');
            }

            $isCash = $paymentType === 'cash';
            $paymentStatus = $isCash ? 'paid' : 'unpaid';
            $paidAmount = $isCash ? $total : 0;

            $purchase = Purchase::create([
                'invoice_number' => Purchase::generateInvoiceNumber(),
                'supplier_id' => $supplierId,
                'user_id' => Auth::id() ?? User::query()->first()?->id ?? 1,
                'tanggal' => $tanggal,
                'payment_type' => $paymentType,
                'due_date' => $paymentType === 'credit' ? $dueDate : null,
                'supplier_invoice' => $supplierInvoice,
                'payment_status' => $paymentStatus,
                'paid_amount' => $paidAmount,
                'total' => $total,
                'status' => 'received',
                'keterangan' => $keterangan,
                'foto_nota' => $fotoNotaPath,
            ]);

            foreach ($items as $item) {
                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Tambah stok
                $product = Product::query()->lockForUpdate()->find($item['product_id']);

                if (! $product) {
                    throw new \RuntimeException(
                        "Produk tidak ditemukan (ID: {$item['product_id']})."
                    );
                }

                if (! empty($item['update_harga_beli'])) {
                    $oldPrice = (float) $product->harga_beli;
                    $newPrice = (float) $item['harga'];
                    $product->update(['harga_beli' => $newPrice]);
                    $this->priceChangeService->record($product, $oldPrice, $newPrice, 'purchase', $purchase);
                }
                $this->stockService->recordMovement(
                    $product,
                    'in',
                    $item['qty'],
                    Purchase::class,
                    $purchase->id,
                    'Pembelian '.$purchase->invoice_number
                );
            }

            // Jika tunai, catat pelunasan awal ke purchase_payments
            if ($isCash && $total > 0) {
                PurchasePayment::create([
                    'purchase_id' => $purchase->id,
                    'supplier_id' => $supplierId,
                    'payment_number' => PurchasePayment::generatePaymentNumber(),
                    'tanggal_bayar' => $tanggal,
                    'nominal' => $total,
                    'metode' => 'cash',
                    'bank_tujuan' => null,
                    'nomor_referensi' => 'LUNAS SAAT BARANG MASUK',
                    'catatan' => 'Pembayaran tunai lunas saat penerimaan barang',
                    'created_by' => Auth::id() ?? $purchase->user_id,
                ]);
            }

            return $purchase;
        });
    }

    public function getPurchases($startDate = null, $endDate = null, $supplierId = null)
    {
        $query = Purchase::with(['supplier', 'user', 'items.product'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->paginate(15);
    }

    public function recordPayment(
        Purchase $purchase,
        float $nominal,
        string $tanggal,
        string $metode,
        ?string $bankTujuan = null,
        ?string $nomorReferensi = null,
        $buktiBayar = null,
        ?string $catatan = null
    ): PurchasePayment {
        return DB::transaction(function () use ($purchase, $nominal, $tanggal, $metode, $bankTujuan, $nomorReferensi, $buktiBayar, $catatan) {
            /** @var Purchase $lockedPurchase */
            $lockedPurchase = Purchase::query()
                ->with(['returns', 'payments'])
                ->lockForUpdate()
                ->find($purchase->id);

            if (! $lockedPurchase) {
                throw new \RuntimeException('Data pembelian tidak ditemukan.');
            }

            $totalReturns = (float) $lockedPurchase->returns->sum('total_nilai');
            $sisaTagihan = (float) $lockedPurchase->total - $totalReturns - (float) $lockedPurchase->paid_amount;
            $sisaTagihan = max(0, round($sisaTagihan, 2));

            if ($nominal <= 0) {
                throw ValidationException::withMessages([
                    'nominal' => 'Nominal pembayaran harus lebih besar dari 0.',
                ]);
            }

            if (round($nominal, 2) > $sisaTagihan) {
                $formattedSisa = 'Rp ' . number_format($sisaTagihan, 0, ',', '.');
                throw ValidationException::withMessages([
                    'nominal' => "Nominal pembayaran (Rp ".number_format($nominal, 0, ',', '.').") melebihi sisa tagihan ({$formattedSisa}).",
                ]);
            }

            $buktiBayarPath = null;
            if ($buktiBayar) {
                $buktiBayarPath = $buktiBayar->store('purchases/payments', 'public');
            }

            $payment = PurchasePayment::create([
                'purchase_id' => $lockedPurchase->id,
                'supplier_id' => $lockedPurchase->supplier_id,
                'payment_number' => PurchasePayment::generatePaymentNumber(),
                'tanggal_bayar' => $tanggal,
                'nominal' => $nominal,
                'metode' => $metode,
                'bank_tujuan' => $bankTujuan,
                'nomor_referensi' => $nomorReferensi,
                'bukti_bayar' => $buktiBayarPath,
                'catatan' => $catatan,
                'created_by' => Auth::id() ?? $lockedPurchase->user_id,
            ]);

            $newPaidAmount = round((float) $lockedPurchase->paid_amount + $nominal, 2);
            $newPaymentStatus = ($newPaidAmount + $totalReturns >= (float) $lockedPurchase->total) ? 'paid' : 'partial';

            $lockedPurchase->update([
                'paid_amount' => $newPaidAmount,
                'payment_status' => $newPaymentStatus,
            ]);

            app(ActivityLogger::class)->log(
                'purchase.payment',
                'Pembayaran tagihan '.$lockedPurchase->invoice_number.' dicatat sebesar Rp '.number_format($nominal, 0, ',', '.').' (Status: '.strtoupper($newPaymentStatus).').'
            );

            return $payment;
        });
    }

    public function getSupplierBills(
        ?string $status = null,
        ?int $supplierId = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $search = null
    ) {
        $today = now()->toDateString();
        $query = Purchase::with(['supplier', 'user', 'returns', 'payments.createdBy'])
            ->orderByRaw("CASE WHEN payment_status = 'unpaid' THEN 1 WHEN payment_status = 'partial' THEN 2 ELSE 3 END")
            ->orderByRaw("CASE WHEN due_date IS NOT NULL AND due_date < '{$today}' AND payment_status != 'paid' THEN 0 ELSE 1 END")
            ->orderBy('due_date', 'asc')
            ->orderBy('tanggal', 'desc');

        if ($status === 'unpaid') {
            $query->where('payment_status', 'unpaid');
        } elseif ($status === 'partial') {
            $query->where('payment_status', 'partial');
        } elseif ($status === 'paid') {
            $query->where('payment_status', 'paid');
        } elseif ($status === 'overdue') {
            $query->where('payment_status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now()->toDateString());
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate(15)->withQueryString();
    }

    public function getSupplierBillMetrics(): array
    {
        $allPurchases = Purchase::with('returns')->get();

        $totalHutang = 0;
        $overdueCount = 0;
        $overdueAmount = 0;
        $upcomingCount = 0;
        $upcomingAmount = 0;
        $now = now()->startOfDay();
        $in7Days = now()->addDays(7)->endOfDay();

        foreach ($allPurchases as $p) {
            if ($p->payment_status !== 'paid') {
                $sisa = $p->remaining_bill;
                $totalHutang += $sisa;

                if ($p->due_date) {
                    $dueDate = $p->due_date->startOfDay();
                    if ($dueDate->lt($now)) {
                        $overdueCount++;
                        $overdueAmount += $sisa;
                    } elseif ($dueDate->gte($now) && $dueDate->lte($in7Days)) {
                        $upcomingCount++;
                        $upcomingAmount += $sisa;
                    }
                }
            }
        }

        $lunasBulanIni = (float) PurchasePayment::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal');

        return [
            'total_hutang' => $totalHutang,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'upcoming_count' => $upcomingCount,
            'upcoming_amount' => $upcomingAmount,
            'lunas_bulan_ini' => $lunasBulanIni,
        ];
    }

    public function processReturn(Purchase $purchase, array $items, string $alasan, ?string $tanggal = null): PurchaseReturn
    {
        $purchaseReturn = DB::transaction(function () use ($purchase, $items, $alasan, $tanggal) {
            $prepared = [];
            $totalNilai = 0;

            foreach ($items as $item) {
                if (! isset($item['qty']) || (int) $item['qty'] <= 0) {
                    continue;
                }

                $purchaseItem = $purchase->items()
                    ->where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $purchaseItem) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tersebut tidak ada pada transaksi pembelian ini.',
                    ]);
                }

                $qty = (int) $item['qty'];
                $sisa = $purchaseItem->qty - $purchaseItem->returned_qty;

                if ($qty > $sisa) {
                    throw ValidationException::withMessages([
                        'items' => "Qty retur melebihi sisa yang dapat diretur untuk {$purchaseItem->product->name} (sisa {$sisa}).",
                    ]);
                }

                // Periksa kecukupan stok fisik di gudang/toko
                $product = Product::query()->lockForUpdate()->find($purchaseItem->product_id);
                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak ditemukan.',
                    ]);
                }

                if ($product->stok < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok di toko untuk produk {$product->name} (sisa {$product->stok}) tidak mencukupi untuk diretur sebanyak {$qty} unit ke supplier.",
                    ]);
                }

                $subtotal = (float) $purchaseItem->harga * $qty;
                $totalNilai += $subtotal;

                $prepared[] = [
                    'purchaseItem' => $purchaseItem,
                    'product' => $product,
                    'qty' => $qty,
                    'harga' => $purchaseItem->harga,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($prepared)) {
                throw ValidationException::withMessages([
                    'items' => 'Pilih minimal satu produk dengan qty lebih dari 0 untuk diretur.',
                ]);
            }

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'return_number' => PurchaseReturn::generateReturnNumber(),
                'tanggal' => $tanggal ?: date('Y-m-d'),
                'total_nilai' => $totalNilai,
                'alasan' => $alasan,
                'status' => 'completed',
                'processed_by' => Auth::id(),
            ]);

            foreach ($prepared as $entry) {
                $purchaseItem = $entry['purchaseItem'];
                $product = $entry['product'];
                $qty = $entry['qty'];

                $purchaseReturn->items()->create([
                    'purchase_item_id' => $purchaseItem->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'harga' => $entry['harga'],
                    'subtotal' => $entry['subtotal'],
                ]);

                $purchaseItem->increment('returned_qty', $qty);

                // Catat mutasi stok keluar (out) karena barang dikembalikan ke supplier
                $this->stockService->recordMovement(
                    $product,
                    'out',
                    $qty,
                    PurchaseReturn::class,
                    $purchaseReturn->id,
                    'Retur Pembelian '.$purchaseReturn->return_number
                );
            }

            // Update status pembelian jika semua item telah diretur penuh
            $isFullyReturned = $purchase->items()->whereColumn('returned_qty', '<', 'qty')->doesntExist();
            $purchase->update(['status' => $isFullyReturned ? 'returned' : 'partial_return']);

            // Update status pembayaran jika tagihan tertutup retur
            $totalReturns = (float) $purchase->returns()->sum('total_nilai');
            if ((float) $purchase->paid_amount + $totalReturns >= (float) $purchase->total) {
                $purchase->update(['payment_status' => 'paid']);
            }

            return $purchaseReturn;
        });

        app(ActivityLogger::class)->log(
            'purchase.return',
            'Retur Pembelian '.$purchaseReturn->return_number.' untuk '.$purchase->invoice_number.' (Nilai: Rp '.number_format($purchaseReturn->total_nilai, 0, ',', '.').') ke supplier '.$purchase->supplier->name
        );

        return $purchaseReturn;
    }

    public function getPurchaseReturns($startDate = null, $endDate = null, $supplierId = null)
    {
        $query = PurchaseReturn::with(['purchase', 'supplier', 'processedBy', 'items.product'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        return $query->paginate(15);
    }
}
