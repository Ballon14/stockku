<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\ActivityLogger;
use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\StockService;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CategoryService $categoryService,
        protected StockService $stockService,
    ) {}

    public function index()
    {
        $search = request('search');
        $categoryId = request('category_id');
        $products = $this->productService->getAll($search, $categoryId);
        $categories = $this->categoryService->getActive();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function create()
    {
        $categories = $this->categoryService->getActive();

        return view('products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $product = $this->productService->store(
            $request->validated(),
            $request->file('foto')
        );
        app(ActivityLogger::class)->log('product.create', 'Produk "'.$product->name.'" (SKU: '.$product->sku.') ditambahkan.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('category');
        $stockMovements = $this->stockService->getMovements($product->id);

        return view('products.show', compact('product', 'stockMovements'));
    }

    public function edit(Product $product)
    {
        $categories = $this->categoryService->getActive();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->productService->update(
            $product,
            $request->validated(),
            $request->file('foto')
        );
        app(ActivityLogger::class)->log('product.update', 'Produk "'.$product->name.'" (SKU: '.$product->sku.') diperbarui.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        try {
            $this->productService->delete($product);
        } catch (\RuntimeException $e) {
            return redirect()->route('products.index')->with('error', $e->getMessage());
        }
        app(ActivityLogger::class)->log('product.delete', 'Produk "'.$product->name.'" (SKU: '.$product->sku.') dihapus.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function exportCsv()
    {
        $products = \App\Models\Product::with('category')->get();
        $filename = "produk_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Nama Produk', 'Kategori', 'Harga Beli', 'Harga Jual', 'Stok', 'Min Stok', 'Satuan'];

        $callback = function() use($products, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($products as $product) {
                $row = [
                    $product->name,
                    $product->category->name ?? '',
                    $product->harga_beli,
                    $product->harga_jual,
                    $product->stok,
                    $product->min_stok,
                    $product->satuan
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle); // Read and skip the header

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $importedCount = 0;
            while (($row = fgetcsv($handle)) !== false) {
                $name = trim($row[0] ?? '');
                $catName = trim($row[1] ?? '');
                $hargaBeli = trim($row[2] ?? 0);
                $hargaJual = trim($row[3] ?? 0);
                $stok = trim($row[4] ?? 0);
                $minStok = trim($row[5] ?? 0);
                $satuan = trim($row[6] ?? 'pcs');
                
                if (empty($name) || empty($catName)) continue;

                $category = \App\Models\Category::firstOrCreate(
                    ['name' => $catName],
                    ['slug' => \Illuminate\Support\Str::slug($catName), 'is_active' => true]
                );

                $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $category->name), 0, 3));
                if (strlen($prefix) < 3) {
                    $prefix = str_pad($prefix, 3, 'X');
                }
                
                $lastProduct = \App\Models\Product::where('sku', 'like', "{$prefix}-%")->orderBy('id', 'desc')->first();
                $nextNumber = 1;
                if ($lastProduct) {
                    $parts = explode('-', $lastProduct->sku);
                    if (count($parts) > 1) {
                        $nextNumber = (int) end($parts) + 1;
                    }
                }
                
                do {
                    $sku = $prefix . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                    $nextNumber++;
                } while (\App\Models\Product::where('sku', $sku)->exists());

                $data = [
                    'category_id' => $category->id,
                    'name' => $name,
                    'sku' => $sku,
                    'harga_beli' => (float)$hargaBeli,
                    'harga_jual' => (float)$hargaJual,
                    'stok' => (int)$stok,
                    'min_stok' => (int)$minStok,
                    'satuan' => $satuan,
                    'is_active' => true,
                ];

                $this->productService->store($data);
                $importedCount++;
            }
            \Illuminate\Support\Facades\DB::commit();
            app(ActivityLogger::class)->log('product.import', "{$importedCount} produk diimpor dari CSV.");
            return redirect()->route('products.index')->with('success', "Berhasil mengimpor {$importedCount} produk.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('products.index')->with('error', 'Gagal mengimpor: ' . $e->getMessage());
        } finally {
            fclose($handle);
        }
    }
}
