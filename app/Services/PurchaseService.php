<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
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

    public function createPurchase(int $supplierId, string $tanggal, array $items, ?string $keterangan = null, $fotoNota = null): Purchase
    {
        return DB::transaction(function () use ($supplierId, $tanggal, $items, $keterangan, $fotoNota) {
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

            $purchase = Purchase::create([
                'invoice_number' => Purchase::generateInvoiceNumber(),
                'supplier_id' => $supplierId,
                'user_id' => Auth::id(),
                'tanggal' => $tanggal,
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
