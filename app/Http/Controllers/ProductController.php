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
            $request->except('foto'),
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
            $request->except('foto'),
            $request->file('foto')
        );
        app(ActivityLogger::class)->log('product.update', 'Produk "'.$product->name.'" (SKU: '.$product->sku.') diperbarui.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        app(ActivityLogger::class)->log('product.delete', 'Produk "'.$product->name.'" (SKU: '.$product->sku.') dihapus.');

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
