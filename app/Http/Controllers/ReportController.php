<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $userId = $request->input('user_id');
        $productId = $request->input('product_id');

        $data = $this->reportService->getSalesReport($startDate, $endDate, $userId, $productId);
        $cashiers = User::role(['admin', 'kasir'])->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        if ($request->input('export') === 'pdf') {
            $data = $this->reportService->getSalesReport($startDate, $endDate, $userId, $productId, false);
            $pdf = Pdf::loadView('reports.sales-pdf', compact('data', 'startDate', 'endDate'));

            return $pdf->download('laporan-penjualan.pdf');
        }

        return view('reports.sales', compact('data', 'startDate', 'endDate', 'userId', 'productId', 'cashiers', 'products'));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $data = $this->reportService->getProfitLossReport($startDate, $endDate);

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.profit-loss-pdf', compact('data', 'startDate', 'endDate'));

            return $pdf->download('laporan-laba-rugi.pdf');
        }

        return view('reports.profit-loss', compact('data', 'startDate', 'endDate'));
    }

    public function stock(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $productId = $request->input('product_id');

        $movements = $this->reportService->getStockReport($startDate, $endDate, $productId);
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('reports.stock', compact('movements', 'startDate', 'endDate', 'productId', 'products'));
    }

    public function attendance(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $employeeId = $request->input('employee_id');

        $data = $this->reportService->getAttendanceReport($startDate, $endDate, $employeeId);
        $employees = Employee::where('is_active', true)->orderBy('nama')->get();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.attendance-pdf', compact('data', 'startDate', 'endDate'));

            return $pdf->download('laporan-absensi.pdf');
        }

        return view('reports.attendance', compact('data', 'startDate', 'endDate', 'employeeId', 'employees'));
    }
}
