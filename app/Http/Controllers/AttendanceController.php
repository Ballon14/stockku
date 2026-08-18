<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\ActivityLogger;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $month = request('month', now()->month);
        $year = request('year', now()->year);
        $attendances = $this->attendanceService->getEmployeeAttendances($employee, $month, $year);
        $todayAttendance = $this->attendanceService->getTodayAttendance($employee);

        return view('attendances.index', compact('attendances', 'todayAttendance', 'month', 'year'));
    }

    public function clock()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $todayAttendance = $this->attendanceService->getTodayAttendance($employee);

        return view('attendances.clock', compact('todayAttendance'));
    }

    public function clockIn()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $this->attendanceService->clockIn($employee);
        app(ActivityLogger::class)->log('attendance.clock_in', 'Clock-in ('.$employee->nama.').');

        return redirect()->route('attendance.clock')->with('success', 'Clock-in berhasil!');
    }

    public function clockOut()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $this->attendanceService->clockOut($employee);
        app(ActivityLogger::class)->log('attendance.clock_out', 'Clock-out ('.$employee->nama.').');

        return redirect()->route('attendance.clock')->with('success', 'Clock-out berhasil!');
    }

    public function adminIndex()
    {
        $date = request('date', now()->toDateString());
        $month = request('month', now()->month);
        $year = request('year', now()->year);
        $viewType = request('view', 'daily');

        if ($viewType === 'daily') {
            $attendances = $this->attendanceService->getAllAttendances($date);
        } else {
            $attendances = $this->attendanceService->getAllAttendances(null, $month, $year);
        }

        $employees = Employee::where('is_active', true)->orderBy('nama')->paginate(15)->withQueryString();

        return view('attendances.admin-index', compact('attendances', 'employees', 'date', 'month', 'year', 'viewType'));
    }
}
