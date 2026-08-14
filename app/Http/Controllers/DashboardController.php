<?php

namespace App\Http\Controllers;

use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'manager'])) {
            $data = $this->reportService->getDashboardData();
            return view('dashboard.admin', compact('data'));
        }

        if ($user->hasRole('kasir')) {
            $data = $this->reportService->getDashboardData();
            return view('dashboard.admin', compact('data'));
        }

        // Karyawan/staff
        $employee = $user->employee;
        $todayAttendance = null;
        $recentAttendances = collect();

        if ($employee) {
            $todayAttendance = app(\App\Services\AttendanceService::class)->getTodayAttendance($employee);
            $recentAttendances = $employee->attendances()->latest('tanggal')->limit(7)->get();
        }

        return view('dashboard.employee', compact('todayAttendance', 'recentAttendances'));
    }
}
