<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use App\Services\SupplierService;
use App\Models\Product;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected SupplierService $supplierService,
    ) {}

    public function index()
    {
        $startDate = request('start_date');
        $endDate = request('end_date');
        $supplierId = request('supplier_id');
        $purchases = $this->purchaseService->getPurchases($startDate, $endDate, $supplierId);
        $suppliers = $this->supplierService->getActive();
        return view('purchases.index', compact('purchases', 'suppliers', 'startDate', 'endDate', 'supplierId'));
    }

    public function create()
    {
        $suppliers = $this->supplierService->getActive();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(PurchaseRequest $request)
    {
        $this->purchaseService->createPurchase(
            $request->input('supplier_id'),
            $request->input('tanggal'),
            $request->input('items'),
            $request->input('keterangan')
        );

        return redirect()->route('purchases.index')->with('success', 'Pembelian berhasil dicatat.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }
}
