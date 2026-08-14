<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
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
        $this->productService->store(
            $request->except('foto'),
            $request->file('foto')
        );
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
        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
