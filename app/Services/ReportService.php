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

    public function getAttendanceReport($startDate, $endDate, $employeeId = null)
    {
        $query = Employee::where('is_active', true);

        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        $employees = $query->get();

        $attendances = Attendance::whereBetween('tanggal', [$startDate, $endDate])
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get();

        $data = [];
        foreach ($employees as $employee) {
            $empAtt = $attendances->where('employee_id', $employee->id);
            $totalDays = $empAtt->count();
            $hadir = $empAtt->where('status', 'hadir')->count();

            $data[] = [
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

        return collect($data)->sortByDesc('attendance_percentage')->values()->all();
    }

    public function getDashboardData()
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Penjualan hari ini
        $salesToday = Sale::whereDate('created_at', $today)
            ->where('status', '!=', 'returned')
            ->sum('grand_total');

        $salesCountToday = Sale::whereDate('created_at', $today)
            ->where('status', '!=', 'returned')
            ->count();

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
            ->get();

        // Jendela 7 hari (zona waktu lokal server) agar label grafik selalu sinkron
        $chartWindow = collect(range(6, 0))->map(function ($offset) use ($today) {
            $date = $today->copy()->subDays($offset);

            return [
                'date' => $date->toDateString(),
                'label' => $date->translatedFormat('d M'),
            ];
        });

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

        // Produk stok menipis
        $lowStock = Product::whereColumn('stok', '<=', 'min_stok')
            ->where('is_active', true)
            ->with('category')
            ->limit(10)
            ->get();

        // Kehadiran hari ini
        $attendanceSummary = app(AttendanceService::class)->getTodaySummary();

        // Penjualan bulan ini
        $salesThisMonth = Sale::whereDate('created_at', '>=', $startOfMonth)
            ->where('status', '!=', 'returned')
            ->sum('grand_total');

        return [
            'sales_today' => $salesToday,
            'sales_count_today' => $salesCountToday,
            'sales_this_month' => $salesThisMonth,
            'daily_sales' => $dailySales,
            'chart_window' => $chartWindow,
            'top_products' => $topProducts,
            'low_stock' => $lowStock,
            'attendance_summary' => $attendanceSummary,
        ];
    }
}
