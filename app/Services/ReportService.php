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
        $query = Sale::with(['user', 'items.product.category'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', '!=', 'returned');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->get();

        $items = collect();
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                if ($productId && $item->product_id != $productId) {
                    continue;
                }

                $existing = $items->firstWhere('product_id', $item->product_id);
                if ($existing) {
                    $existing->qty += $item->qty;
                    $existing->subtotal += $item->subtotal;
                } else {
                    $newItem = (object) [
                        'product_id' => $item->product_id,
                        'sku' => $item->product->sku,
                        'name' => $item->product->name,
                        'category_name' => $item->product->category->name ?? '-',
                        'qty' => $item->qty,
                        'subtotal' => $item->subtotal,
                    ];
                    $items->push($newItem);
                }
            }
        }

        $totalRevenue = $items->sum('subtotal');
        $totalTransactions = $sales->filter(function ($sale) use ($productId) {
            if (! $productId) {
                return true;
            }

            return $sale->items->contains('product_id', $productId);
        })->count();

        $items = $items->sortByDesc('qty')->values();

        if (! $paginate) {
            return [
                'summary' => [
                    'total_transactions' => $totalTransactions,
                    'total_revenue' => $totalRevenue,
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
            ],
            'items' => $paginated,
        ];
    }

    public function getProfitLossReport($startDate, $endDate)
    {
        $sales = Sale::with('items.product')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
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
            $q->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
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
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

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
        $salesToday = Sale::whereDate('created_at', $today)
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
            ->whereDate('created_at', '>=', $today->copy()->subDays(6))
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
            ->whereDate('created_at', '>=', $today->copy()->subDays(29))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Grafik penjualan 12 bulan terakhir (agregasi di PHP agar kompatibel semua driver)
        $monthlySales = Sale::where('status', '!=', 'returned')
            ->whereDate('created_at', '>=', $today->copy()->subMonths(11)->startOfMonth())
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
                $q->whereDate('created_at', '>=', $startOfMonth)
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
        $salesThisMonth = Sale::whereDate('created_at', '>=', $startOfMonth)
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
}
