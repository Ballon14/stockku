<?php

namespace App\Services;

use App\Models\PriceChangeLog;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PriceChangeService
{
    /**
     * Record a price change for a product.
     *
     * @param  Product       $product        The product whose price changed.
     * @param  float         $hargaLama      The old price.
     * @param  float         $hargaBaru      The new price.
     * @param  string        $sumber         Source: 'purchase' | 'manual_edit' | 'sync_master'.
     * @param  Model|null    $reference      Optional polymorphic reference (e.g. Purchase).
     * @param  string        $tipeHarga      Price type: 'beli' | 'jual'.
     */
    public function record(
        Product $product,
        float $hargaLama,
        float $hargaBaru,
        string $sumber,
        ?Model $reference = null,
        string $tipeHarga = 'beli',
    ): ?PriceChangeLog {
        // Don't log if price didn't actually change
        if ((float) $hargaLama === (float) $hargaBaru) {
            return null;
        }

        return PriceChangeLog::create([
            'product_id' => $product->id,
            'tipe_harga' => $tipeHarga,
            'harga_lama' => $hargaLama,
            'harga_baru' => $hargaBaru,
            'sumber' => $sumber,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'user_id' => Auth::id(),
        ]);
    }
}
