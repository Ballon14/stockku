<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Services\PurchaseService;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class PurchaseReturnController extends Controller
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

        $returns = $this->purchaseService->getPurchaseReturns($startDate, $endDate, $supplierId);
        $suppliers = $this->supplierService->getActive();

        return view('purchase-returns.index', compact('returns', 'suppliers', 'startDate', 'endDate', 'supplierId'));
    }

    public function create(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        // Jika semua item sudah habis diretur
        $hasReturnableItems = $purchase->items->contains(function ($item) {
            return ($item->qty - $item->returned_qty) > 0;
        });

        if (! $hasReturnableItems) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Semua produk pada transaksi pembelian ini telah diretur penuh.');
        }

        return view('purchase-returns.create', compact('purchase'));
    }

    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'alasan' => 'required|string|max:1000',
            'tanggal' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:0',
        ]);

        $purchaseReturn = $this->purchaseService->processReturn(
            $purchase,
            $request->input('items'),
            $request->input('alasan'),
            $request->input('tanggal')
        );

        return redirect()->route('purchase-returns.show', $purchaseReturn)->with('success', 'Retur pembelian berhasil diproses.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'supplier', 'processedBy', 'items.product']);

        return view('purchase-returns.show', compact('purchaseReturn'));
    }
}
