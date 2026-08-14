<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function createSale(array $items, float $diskon, float $bayar, ?string $catatan = null): Sale
    {
        return DB::transaction(function () use ($items, $diskon, $bayar, $catatan) {
            $subtotal = 0;

            foreach ($items as &$item) {
                $product = Product::findOrFail($item['product_id']);
                $itemSubtotal = ($product->harga_jual * $item['qty']) - ($item['diskon'] ?? 0);
                $item['harga'] = $product->harga_jual;
                $item['subtotal'] = $itemSubtotal;
                $subtotal += $itemSubtotal;
            }

            $grandTotal = $subtotal - $diskon;
            $kembalian = $bayar - $grandTotal;

            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'user_id' => Auth::id(),
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'grand_total' => $grandTotal,
                'bayar' => $bayar,
                'kembalian' => $kembalian,
                'status' => 'completed',
                'catatan' => $catatan,
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'harga' => $item['harga'],
                    'diskon' => $item['diskon'] ?? 0,
                    'subtotal' => $item['subtotal'],
                ]);

                // Kurangi stok
                $product = Product::find($item['product_id']);
                $this->stockService->recordMovement(
                    $product,
                    'out',
                    $item['qty'],
                    Sale::class,
                    $sale->id,
                    'Penjualan ' . $sale->invoice_number
                );
            }

            return $sale;
        });
    }

    public function processReturn(Sale $sale, array $returnItems, string $alasan): SaleReturn
    {
        return DB::transaction(function () use ($sale, $returnItems, $alasan) {
            $totalRefund = 0;

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_number' => SaleReturn::generateReturnNumber(),
                'alasan' => $alasan,
                'status' => 'approved',
                'processed_by' => Auth::id(),
            ]);

            foreach ($returnItems as $item) {
                $saleItem = $sale->items()->where('product_id', $item['product_id'])->first();
                if (!$saleItem) continue;

                $refund = $saleItem->harga * $item['qty'];
                $totalRefund += $refund;

                $saleReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'harga' => $saleItem->harga,
                    'subtotal' => $refund,
                ]);

                // Kembalikan stok
                $product = Product::find($item['product_id']);
                $this->stockService->recordMovement(
                    $product,
                    'return',
                    $item['qty'],
                    SaleReturn::class,
                    $saleReturn->id,
                    'Retur ' . $saleReturn->return_number
                );
            }

            $saleReturn->update(['total_refund' => $totalRefund]);

            // Update sale status
            $sale->update(['status' => 'returned']);

            return $saleReturn;
        });
    }

    public function getSales($startDate = null, $endDate = null, $userId = null)
    {
        $query = Sale::with(['user', 'items.product'])->latest('id');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->paginate(15);
    }
}
