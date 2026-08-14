<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;

class StockService
{
    public function recordMovement(
        Product $product,
        string $type,
        int $qty,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $keterangan = null
    ): StockMovement {
        $stokSebelum = $product->stok;

        if ($type === 'in' || $type === 'return') {
            $product->increment('stok', $qty);
        } elseif ($type === 'out') {
            $product->decrement('stok', $qty);
        }

        $product->refresh();

        return StockMovement::create([
            'product_id' => $product->id,
            'type' => $type,
            'qty' => $qty,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $product->stok,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'keterangan' => $keterangan,
            'user_id' => Auth::id(),
        ]);
    }

    public function getMovements(int $productId, $startDate = null, $endDate = null)
    {
        $query = StockMovement::where('product_id', $productId)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query->paginate(20);
    }
}
