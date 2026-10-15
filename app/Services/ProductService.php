<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function __construct(
        protected PriceChangeService $priceChangeService,
    ) {}
    public function getAll($search = null, $categoryId = null)
    {
        $query = Product::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($qCategory) use ($search) {
                        $qCategory->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->latest()->paginate(15);
    }

    public function store(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        // Detect harga_beli change before updating
        $oldHargaBeli = (float) $product->harga_beli;

        $product->update($data);

        // Log price change if harga_beli actually changed
        if (isset($data['harga_beli'])) {
            $newHargaBeli = (float) $data['harga_beli'];
            $this->priceChangeService->record($product, $oldHargaBeli, $newHargaBeli, 'manual_edit');
        }

        return $product;
    }

    public function delete(Product $product): bool
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists() || $product->stockMovements()->exists()) {
            throw new \RuntimeException('Produk tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        return $product->delete();
    }

    public function getLowStock()
    {
        return Product::whereColumn('stok', '<=', 'min_stok')
            ->where('is_active', true)
            ->with('category')
            ->get();
    }

    public function searchForPos(string $search)
    {
        return Product::where('is_active', true)
            ->where('stok', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();
    }
}
