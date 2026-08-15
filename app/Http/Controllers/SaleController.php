<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index()
    {
        $startDate = request('start_date');
        $endDate = request('end_date');
        $userId = request('user_id');

        // Kasir hanya bisa lihat transaksinya sendiri
        if (auth()->user()->hasRole('kasir')) {
            $userId = auth()->id();
        }

        $sales = $this->saleService->getSales($startDate, $endDate, $userId);
        $cashiers = User::role(['admin', 'kasir'])->get();

        return view('sales.index', compact('sales', 'cashiers', 'startDate', 'endDate', 'userId'));
    }

    public function show(Sale $sale)
    {
        // Kasir hanya bisa lihat transaksinya sendiri
        if (auth()->user()->hasRole('kasir') && $sale->user_id !== auth()->id()) {
            abort(403);
        }

        $sale->load(['items.product', 'user', 'returns.items']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product', 'user']);

        return view('sales.receipt', compact('sale'));
    }

    public function pos()
    {
        return view('sales.pos');
    }
}
