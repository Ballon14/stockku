<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Services\ActivityLogger;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'manager'])) {
            $leaveRequests = LeaveRequest::with('employee')
                ->latest()
                ->paginate(15);
        } else {
            $employee = $user->employee;
            if (! $employee) {
                return redirect()->route('dashboard')->with('error', 'Data karyawan tidak ditemukan.');
            }
            $leaveRequests = $employee->leaveRequests()
                ->latest()
                ->paginate(15);
        }

        return view('leave-requests.index', compact('leaveRequests'));
    }

    public function create()
    {
        return view('leave-requests.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis' => 'required|in:izin,sakit,cuti',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $employee = auth()->user()->employee;
        if (! $employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $leaveRequest = $this->attendanceService->createLeaveRequest($employee, $request->only([
            'jenis', 'tanggal_mulai', 'tanggal_selesai', 'keterangan',
        ]));

        app(ActivityLogger::class)->log('leave.create', 'Pengajuan '.$leaveRequest->jenis.' ('.$leaveRequest->tanggal_mulai.' s/d '.$leaveRequest->tanggal_selesai.') dibuat.');

        return redirect()->route('leave-requests.index')->with('success', 'Pengajuan berhasil dikirim.');
    }

    public function approve(LeaveRequest $leaveRequest, Request $request)
    {
        $this->attendanceService->approveLeaveRequest(
            $leaveRequest,
            auth()->id(),
            $request->input('catatan_approval')
        );

        app(ActivityLogger::class)->log('leave.approve', 'Pengajuan '.$leaveRequest->jenis.' milik '.$leaveRequest->employee->nama.' disetujui.');

        return back()->with('success', 'Pengajuan disetujui.');
    }

    public function reject(LeaveRequest $leaveRequest, Request $request)
    {
        $this->attendanceService->rejectLeaveRequest(
            $leaveRequest,
            auth()->id(),
            $request->input('catatan_approval')
        );

        app(ActivityLogger::class)->log('leave.reject', 'Pengajuan '.$leaveRequest->jenis.' milik '.$leaveRequest->employee->nama.' ditolak.');

        return back()->with('success', 'Pengajuan ditolak.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = auth()->user()->employee;

        if (! $employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Anda tidak berhak membatalkan pengajuan ini.');
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $this->attendanceService->cancelLeaveRequest($leaveRequest, auth()->id());

        app(ActivityLogger::class)->log('leave.cancel', 'Pengajuan '.$leaveRequest->jenis.' ('.$leaveRequest->tanggal_mulai.' s/d '.$leaveRequest->tanggal_selesai.') dibatalkan oleh pengaju.');

        return back()->with('success', 'Pengajuan dibatalkan.');
    }
}
