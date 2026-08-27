<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/download-apk', function () {
    $apk = public_path('downloads/stockku.apk');

    if (! file_exists($apk)) {
        return back()->with('error', 'File aplikasi belum tersedia. Hubungi administrator.');
    }

    return response()->download($apk, 'StockKu.apk');
})->name('downloads.apk');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'ensure-attended'])
    ->name('dashboard');

Route::middleware(['auth', 'ensure-attended'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('products', ProductController::class);
        Route::resource('suppliers', SupplierController::class)->except('show');
        Route::resource('employees', EmployeeController::class)->except('show');
        Route::post('/employees/{employee}/toggle-active', [EmployeeController::class, 'toggleActive'])->name('employees.toggle-active');

        // Purchases
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

        // Stock
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/low', [StockController::class, 'lowStock'])->name('stock.low');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');

        // Sale Returns
        Route::get('/sale-returns', [SaleReturnController::class, 'index'])->name('sale-returns.index');
        Route::get('/sale-returns/create/{sale}', [SaleReturnController::class, 'create'])->name('sale-returns.create');
        Route::post('/sale-returns/{sale}', [SaleReturnController::class, 'store'])->name('sale-returns.store');
        Route::get('/sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sale-returns.show');

        // Attendance Admin
        Route::get('/attendance/admin', [AttendanceController::class, 'adminIndex'])->name('attendance.admin');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // POS - Admin & Kasir
    Route::middleware('role:admin|kasir')->group(function () {
        Route::get('/pos', [SaleController::class, 'pos'])->name('pos');
        Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    });

    // Attendance - All authenticated users with employee data
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/clock', [AttendanceController::class, 'clock'])->name('attendance.clock');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');

    // Leave Requests
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::post('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve')->middleware('role:admin|manager');
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject')->middleware('role:admin|manager');

    // Reports - Admin & Manager & Kasir (Kasir only sees their own sales)
    Route::middleware('role:admin|manager|kasir')->prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('reports.sales');
    });

    Route::middleware('role:admin|manager')->prefix('reports')->group(function () {
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit-loss');
        Route::get('/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('/price-change', [ReportController::class, 'priceChange'])->name('reports.price-change');
    });
});

require __DIR__.'/auth.php';
