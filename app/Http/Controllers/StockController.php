<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;

class StockController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index()
    {
        $productId = request('product_id');
        $startDate = request('start_date');
        $endDate = request('end_date');

        $movements = null;
        $product = null;

        if ($productId) {
            $product = Product::findOrFail($productId);
            $movements = $this->stockService->getMovements($productId, $startDate, $endDate);
        }

        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('movements', 'product', 'products', 'productId', 'startDate', 'endDate'));
    }

    public function lowStock()
    {
        $products = Product::whereColumn('stok', '<=', 'min_stok')
            ->where('is_active', true)
            ->with('category')
            ->paginate(20);

        return view('stock.low-stock', compact('products'));
    }
}
