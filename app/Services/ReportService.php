<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport($startDate, $endDate, $userId = null, $productId = null, $paginate = true)
    {
        $itemsQuery = SaleItem::select(
            'sale_items.product_id',
            DB::raw('SUM(sale_items.qty) as qty'),
            DB::raw('SUM(sale_items.subtotal) as subtotal')
        )
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.created_at', '>=', $startDate)
            ->where('sales.created_at', '<', Carbon::parse($endDate)->addDay())
            ->where('sales.status', '!=', 'returned');

        if ($userId) {
            $itemsQuery->where('sales.user_id', $userId);
        }
        if ($productId) {
            $itemsQuery->where('sale_items.product_id', $productId);
        }

        $itemsQuery->groupBy('sale_items.product_id');

        $aggregated = $itemsQuery->get();

        // Load product data for the aggregated product IDs
        $productIds = $aggregated->pluck('product_id');
        $products = Product::with('category')->whereIn('id', $productIds)->get()->keyBy('id');

        $items = $aggregated->map(function ($row) use ($products) {
            $product = $products->get($row->product_id);

            return (object) [
                'product_id' => $row->product_id,
                'sku' => $product->sku ?? '-',
                'name' => $product->name ?? '-',
                'category_name' => $product->category->name ?? '-',
                'qty' => (int) $row->qty,
                'subtotal' => (float) $row->subtotal,
            ];
        })->sortByDesc('qty')->values();

        $totalRevenue = $items->sum('subtotal');
        $totalItemsSold = $items->sum('qty');

        // Count distinct transactions via SQL
        $txQuery = Sale::where('created_at', '>=', $startDate)
            ->where('created_at', '<', Carbon::parse($endDate)->addDay())
            ->where('status', '!=', 'returned');

        if ($userId) {
            $txQuery->where('user_id', $userId);
        }
        if ($productId) {
            $txQuery->whereHas('items', fn ($q) => $q->where('product_id', $productId));
        }

        $totalTransactions = $txQuery->count();

        if (! $paginate) {
            return [
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_revenue' => $totalRevenue,
                    'total_items_sold' => $totalItemsSold,
                ],
                'items' => $items,
            ];
        }

        $perPage = 25;
        $page = (int) request('page', 1);

        $paginated = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'summary' => [
                'total_transactions' => $totalTransactions,
                'total_revenue' => $totalRevenue,
                'total_items_sold' => $totalItemsSold,
            ],
            'items' => $paginated,
        ];
    }

    public function getProfitLossReport($startDate, $endDate)
    {
        $sales = Sale::with('items.product')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', Carbon::parse($endDate)->addDay())
            ->where('status', '!=', 'returned')
            ->get();

        $revenue = $sales->sum('subtotal');
        $discounts = $sales->sum('diskon');
        $net_revenue = $sales->sum('grand_total');

        $cogs = $sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                $hargaBeli = $item->harga_beli ?? $item->product?->harga_beli ?? 0;

                return $hargaBeli * $item->qty;
            });
        });

        // Retur
        $totalRetur = SaleReturn::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->where('created_at', '>=', $startDate)
                ->where('created_at', '<', Carbon::parse($endDate)->addDay());
        })->where('status', 'approved')->sum('total_refund');

        $gross_profit = $net_revenue - $cogs - $totalRetur;

        return [
            'revenue' => $revenue,
            'discounts' => $discounts,
            'net_revenue' => $net_revenue,
            'cogs' => $cogs,
            'total_retur' => $totalRetur,
            'gross_profit' => $gross_profit,
        ];
    }

    public function getStockReport($startDate, $endDate, $productId = null)
    {
        $query = StockMovement::with(['product', 'user'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<', Carbon::parse($endDate)->addDay());

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getAttendanceReport($startDate, $endDate, $employeeId = null, $paginate = true)
    {
        $query = Employee::where('is_active', true)->orderBy('nama');

        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        $employees = $paginate ? $query->paginate(15) : $query->get();

        $attendances = Attendance::whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get();

        $rows = [];
        foreach ($employees as $employee) {
            $empAtt = $attendances->where('employee_id', $employee->id);
            $totalDays = $empAtt->count();
            $hadir = $empAtt->where('status', 'hadir')->count();

            $rows[] = [
                'employee_name' => $employee->nama,
                'employee_jabatan' => $employee->jabatan,
                'total_days' => $totalDays,
                'hadir' => $hadir,
                'sakit' => $empAtt->where('status', 'sakit')->count(),
                'izin' => $empAtt->where('status', 'izin')->count(),
                'cuti' => $empAtt->where('status', 'cuti')->count(),
                'alpha' => $empAtt->where('status', 'alpha')->count(),
                'attendance_percentage' => $totalDays > 0 ? round(($hadir / $totalDays) * 100) : 0,
            ];
        }

        usort($rows, fn ($a, $b) => $b['attendance_percentage'] <=> $a['attendance_percentage']);

        return [
            'rows' => $rows,
            'employees' => $employees,
        ];
    }

    public function getDashboardData()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Penjualan hari ini
        $salesToday = Sale::whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->where('status', '!=', 'returned')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total, COUNT(*) as jumlah')
            ->first();

        // Grafik penjualan 7 hari terakhir
        $dailySales = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', '!=', 'returned')
            ->where('created_at', '>=', $today->copy()->subDays(6))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Grafik penjualan 30 hari terakhir
        $dailySales30 = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(grand_total) as total'),
            DB::raw('COUNT(*) as count')
        )
            ->where('status', '!=', 'returned')
            ->where('created_at', '>=', $today->copy()->subDays(29))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Grafik penjualan 12 bulan terakhir (agregasi di PHP agar kompatibel semua driver)
        $monthlySales = Sale::where('status', '!=', 'returned')
            ->where('created_at', '>=', $today->copy()->subMonths(11)->startOfMonth())
            ->get(['created_at', 'grand_total'])
            ->groupBy(fn ($sale) => $sale->created_at->format('Y-m'))
            ->map(fn ($items) => ['total' => (float) $items->sum('grand_total'), 'count' => $items->count()]);

        $salesChart = [
            '7d' => $this->buildChartSeries($today->copy()->subDays(6), $today, 'day', $dailySales),
            '30d' => $this->buildChartSeries($today->copy()->subDays(29), $today, 'day', $dailySales30),
            '12m' => $this->buildChartSeries($today->copy()->subMonths(11)->startOfMonth(), $today, 'month', $monthlySales),
        ];

        // Produk terlaris bulan ini
        $topProducts = SaleItem::select(
            'product_id',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(subtotal) as total_sales')
        )
            ->whereHas('sale', function ($q) use ($startOfMonth) {
                $q->where('created_at', '>=', $startOfMonth)
                    ->where('status', '!=', 'returned');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get();

        // Produk stok menipis (daftar dibatasi 10, hitung penuh untuk kartu)
        $lowStock = Product::whereColumn('stok', '<=', 'min_stok')
            ->where('is_active', true)
            ->with('category')
            ->limit(10)
            ->get();

        $lowStockCount = Product::whereColumn('stok', '<=', 'min_stok')
            ->where('is_active', true)
            ->count();

        // Penjualan bulan ini
        $salesThisMonth = Sale::where('created_at', '>=', $startOfMonth)
            ->where('status', '!=', 'returned')
            ->sum('grand_total');

        return [
            'sales_today' => (float) $salesToday->total,
            'sales_count_today' => (int) $salesToday->jumlah,
            'sales_this_month' => $salesThisMonth,
            'sales_chart' => $salesChart,
            'top_products' => $topProducts,
            'low_stock' => $lowStock,
            'low_stock_count' => $lowStockCount,
        ];
    }

    /**
     * Bangun deret grafik (labels + data + total + rata-rata) dari jendela waktu.
     * $raw: Collection berisi data agregat per hari (key 'date'/'total') atau
     * Collection berisi total per bulan (key 'Y-m' => total).
     */
    private function buildChartSeries(Carbon $start, Carbon $end, string $unit, $raw): array
    {
        $labels = [];
        $data = [];
        $counts = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($unit === 'month') {
                $key = $cursor->format('Y-m');
                $label = $cursor->translatedFormat('M');
                $cursor->addMonth();
            } else {
                $key = $cursor->toDateString();
                $label = $cursor->translatedFormat('d M');
                $cursor->addDay();
            }

            $bucket = $raw[$key] ?? null;
            $labels[] = $label;
            $data[] = (float) ($unit === 'month' ? ($bucket['total'] ?? 0) : ($bucket->total ?? 0));
            $counts[] = (int) ($unit === 'month' ? ($bucket['count'] ?? 0) : ($bucket->count ?? 0));
        }

        $total = array_sum($data);
        $count = count($data);

        return [
            'labels' => $labels,
            'data' => $data,
            'counts' => $counts,
            'total' => $total,
            'average' => $count > 0 ? $total / $count : 0,
        ];
    }

    /**
     * Rekap perubahan harga jual produk.
     * Membandingkan harga di sale_items antar transaksi untuk mendeteksi kenaikan/penurunan.
     */
    public function getPriceChangeReport($startDate, $endDate, $productId = null, $paginate = true)
    {
        // Get all purchase items within the date range, ordered by product and date
        $query = \App\Models\PurchaseItem::select('purchase_items.*')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.status', '!=', 'cancelled')
            ->where('purchases.tanggal', '>=', $startDate)
            ->where('purchases.tanggal', '<=', $endDate)
            ->with(['product.category', 'purchase:id,invoice_number,tanggal,user_id', 'purchase.user:id,name'])
            ->orderBy('purchase_items.product_id')
            ->orderBy('purchases.tanggal')
            ->orderBy('purchases.created_at');

        if ($productId) {
            $query->where('purchase_items.product_id', $productId);
        }

        $purchaseItems = $query->get();

        // Group by product and detect price changes
        $changes = collect();
        $grouped = $purchaseItems->groupBy('product_id');

        foreach ($grouped as $prodId => $items) {
            $prevPrice = null;
            $prevDate = null;
            $prevInvoice = null;

            foreach ($items as $item) {
                $currentPrice = (float) $item->harga;

                if ($prevPrice !== null && $currentPrice !== $prevPrice) {
                    $diff = $currentPrice - $prevPrice;
                    $pctChange = $prevPrice > 0 ? ($diff / $prevPrice) * 100 : 0;

                    $changes->push((object) [
                        'product_id' => $prodId,
                        'product_name' => $item->product->name ?? '-',
                        'product_sku' => $item->product->sku ?? '-',
                        'category_name' => $item->product->category->name ?? '-',
                        'harga_lama' => $prevPrice,
                        'harga_baru' => $currentPrice,
                        'selisih' => $diff,
                        'persen' => round($pctChange, 1),
                        'tipe' => $diff > 0 ? 'naik' : 'turun',
                        'tanggal' => Carbon::parse($item->purchase->tanggal),
                        'invoice_sebelumnya' => $prevInvoice,
                        'invoice_perubahan' => $item->purchase->invoice_number,
                        'pencatat' => $item->purchase->user->name ?? '-',
                    ]);
                }

                $prevPrice = $currentPrice;
                $prevDate = $item->purchase->tanggal;
                $prevInvoice = $item->purchase->invoice_number;
            }
        }

        // Also detect "current price vs last sold price" for products that have changed
        // since their last sale (current harga_jual differs from last sale price)
        $productsQuery = Product::where('is_active', true)->with('category');
        if ($productId) {
            $productsQuery->where('id', $productId);
        }
        $products = $productsQuery->get();

        $currentVsLastBought = collect();
        foreach ($products as $product) {
            $lastPurchaseItem = \App\Models\PurchaseItem::select('purchase_items.*')
                ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                ->where('purchases.status', '!=', 'cancelled')
                ->where('purchase_items.product_id', $product->id)
                ->orderByDesc('purchases.tanggal')
                ->orderByDesc('purchases.created_at')
                ->first();

            if ($lastPurchaseItem) {
                $lastBoughtPrice = (float) $lastPurchaseItem->harga;
                $currentPrice = (float) $product->harga_beli;

                if ($currentPrice !== $lastBoughtPrice) {
                    $diff = $currentPrice - $lastBoughtPrice;
                    $pctChange = $lastBoughtPrice > 0 ? ($diff / $lastBoughtPrice) * 100 : 0;

                    $currentVsLastBought->push((object) [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'category_name' => $product->category->name ?? '-',
                        'harga_terakhir_dibeli' => $lastBoughtPrice,
                        'harga_beli_sekarang' => $currentPrice,
                        'selisih' => $diff,
                        'persen' => round($pctChange, 1),
                        'tipe' => $diff > 0 ? 'naik' : 'turun',
                    ]);
                }
            }
        }

        // Sort changes by date descending (newest first)
        $changes = $changes->sortByDesc('tanggal')->values();

        // Summary
        $totalNaik = $changes->where('tipe', 'naik')->count();
        $totalTurun = $changes->where('tipe', 'turun')->count();
        $totalChanges = $changes->count();
        $productsAffected = $changes->pluck('product_id')->unique()->count();

        $summary = [
            'total_changes' => $totalChanges,
            'total_naik' => $totalNaik,
            'total_turun' => $totalTurun,
            'products_affected' => $productsAffected,
        ];

        if (! $paginate) {
            return [
                'summary' => $summary,
                'changes' => $changes,
                'current_vs_last_bought' => $currentVsLastBought,
            ];
        }

        $perPage = 20;
        $page = (int) request('page', 1);

        $paginated = new LengthAwarePaginator(
            $changes->forPage($page, $perPage)->values(),
            $changes->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'summary' => $summary,
            'changes' => $paginated,
            'current_vs_last_bought' => $currentVsLastBought,
        ];
    }
}
