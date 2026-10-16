<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchasePaymentRequest;
use App\Models\Purchase;
use App\Services\PurchaseService;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierBillController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected SupplierService $supplierService,
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $supplierId = $request->query('supplier_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $bills = $this->purchaseService->getSupplierBills($status, $supplierId, $startDate, $endDate, $search);
        $metrics = $this->purchaseService->getSupplierBillMetrics();
        $suppliers = $this->supplierService->getActive();

        return view('supplier-bills.index', compact('bills', 'metrics', 'suppliers', 'status', 'supplierId', 'startDate', 'endDate', 'search'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product', 'returns.items.product', 'returns.processedBy', 'payments.createdBy']);

        return view('supplier-bills.show', compact('purchase'));
    }

    public function storePayment(PurchasePaymentRequest $request, Purchase $purchase)
    {
        $nominal = (float) $request->input('nominal');

        $this->purchaseService->recordPayment(
            $purchase,
            $nominal,
            $request->input('tanggal_bayar'),
            $request->input('metode'),
            $request->input('bank_tujuan'),
            $request->input('nomor_referensi'),
            $request->file('bukti_bayar'),
            $request->input('catatan')
        );

        return redirect()->back()->with('success', 'Pembayaran tagihan sebesar Rp '.number_format($nominal, 0, ',', '.').' berhasil dicatat.');
    }
}
