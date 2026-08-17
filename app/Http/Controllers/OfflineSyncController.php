<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OfflineSyncController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function catalog(): JsonResponse
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'barcode', 'harga_jual', 'stok', 'satuan']);

        return response()->json($products);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'transactions' => ['required', 'array'],
            'transactions.*.offline_id' => ['required', 'string', 'max:100'],
            'transactions.*.items' => ['required', 'array', 'min:1'],
            'transactions.*.items.*.product_id' => ['required', 'integer'],
            'transactions.*.items.*.qty' => ['required', 'integer', 'min:1'],
            'transactions.*.diskon' => ['nullable', 'numeric', 'min:0'],
            'transactions.*.bayar' => ['required', 'numeric', 'min:0'],
            'transactions.*.catatan' => ['nullable', 'string', 'max:255'],
            'transactions.*.payment_method' => ['nullable', 'string', 'in:cash,qris'],
        ]);

        $results = [];

        foreach ($data['transactions'] as $transaction) {
            try {
                $existing = Sale::where('offline_id', $transaction['offline_id'])->first();

                if ($existing) {
                    $results[] = [
                        'offline_id' => $transaction['offline_id'],
                        'sale_id' => $existing->id,
                        'status' => 'success',
                    ];

                    continue;
                }

                $sale = $this->saleService->createSale(
                    $transaction['items'],
                    (float) ($transaction['diskon'] ?? 0),
                    (float) $transaction['bayar'],
                    $transaction['catatan'] ?? null,
                    $transaction['payment_method'] ?? 'cash'
                );

                $sale->update([
                    'sumber' => 'offline',
                    'offline_id' => $transaction['offline_id'],
                ]);

                $results[] = [
                    'offline_id' => $transaction['offline_id'],
                    'sale_id' => $sale->id,
                    'status' => 'success',
                ];
            } catch (ValidationException $e) {
                $results[] = [
                    'offline_id' => $transaction['offline_id'],
                    'status' => 'failed',
                    'message' => collect($e->errors())->flatten()->first(),
                ];
            } catch (QueryException $e) {
                if (! $this->isUniqueViolation($e)) {
                    throw $e;
                }

                $results[] = [
                    'offline_id' => $transaction['offline_id'],
                    'status' => 'failed',
                    'message' => 'Transaksi sudah pernah disinkronkan.',
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        $code = (int) $exception->getCode();

        return in_array($code, [1062, 19, 1555, 1561], true);
    }
}
