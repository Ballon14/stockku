<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function getAll($search = null, $categoryId = null)
    {
        $query = Product::with('category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->latest()->paginate(15);
    }

    public function store(array $data, ?UploadedFile $foto = null): Product
    {
        if ($foto) {
            $data['foto'] = $foto->store('products', 'public');
        }

        return Product::create($data);
    }

    public function update(Product $product, array $data, ?UploadedFile $foto = null): Product
    {
        if ($foto) {
            // Delete old foto
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }
            $data['foto'] = $foto->store('products', 'public');
        }

        $product->update($data);

        return $product;
    }

    public function delete(Product $product): bool
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists() || $product->stockMovements()->exists()) {
            throw new \RuntimeException('Produk tidak dapat dihapus karena memiliki riwayat transaksi.');
        }

        if ($product->foto) {
            Storage::disk('public')->delete($product->foto);
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
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', $search);
            })
            ->limit(10)
            ->get();
    }
}
