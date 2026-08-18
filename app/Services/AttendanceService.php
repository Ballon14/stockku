<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AttendanceService
{
    public function clockIn(Employee $employee): Attendance
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'tanggal' => $today],
            ['clock_in' => $now, 'status' => 'hadir']
        );

        if (! $attendance->wasRecentlyCreated && ! $attendance->clock_in) {
            $attendance->update(['clock_in' => $now, 'status' => 'hadir']);
        }

        return $attendance;
    }

    public function clockOut(Employee $employee): ?Attendance
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('tanggal', $today)
            ->first();

        if ($attendance && $attendance->clock_in && ! $attendance->clock_out) {
            $attendance->update(['clock_out' => $now]);
        }

        return $attendance;
    }

    public function getTodayAttendance(Employee $employee): ?Attendance
    {
        return Attendance::where('employee_id', $employee->id)
            ->where('tanggal', Carbon::today()->toDateString())
            ->first();
    }

    public function getEmployeeAttendances(Employee $employee, $month = null, $year = null)
    {
        $query = Attendance::where('employee_id', $employee->id)
            ->orderBy('tanggal', 'desc');

        if ($month && $year) {
            $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        return $query->paginate(31);
    }

    public function getAllAttendances($date = null, $month = null, $year = null)
    {
        $query = Attendance::with('employee')->orderBy('tanggal', 'desc');

        if ($date) {
            $query->where('tanggal', $date);
        } elseif ($month && $year) {
            $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }

        return $query->get();
    }

    public function getTodaySummary(): array
    {
        $today = Carbon::today()->toDateString();
        $totalEmployees = Employee::where('is_active', true)->count();
        $present = Attendance::where('tanggal', $today)->where('status', 'hadir')->count();

        return [
            'total' => $totalEmployees,
            'hadir' => $present,
            'tidak_hadir' => $totalEmployees - $present,
        ];
    }

    public function createLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'jenis' => $data['jenis'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'keterangan' => $data['keterangan'] ?? null,
            'status' => 'pending',
        ]);
    }

    public function approveLeaveRequest(LeaveRequest $leaveRequest, int $approvedBy, ?string $catatan = null): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'catatan_approval' => $catatan,
        ]);

        // Create attendance records for approved leave
        $start = Carbon::parse($leaveRequest->tanggal_mulai);
        $end = Carbon::parse($leaveRequest->tanggal_selesai);

        for ($date = $start; $date->lte($end); $date->addDay()) {
            Attendance::firstOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'tanggal' => $date->toDateString()],
                ['status' => $leaveRequest->jenis, 'keterangan' => $leaveRequest->keterangan]
            );
        }

        return $leaveRequest;
    }

    public function rejectLeaveRequest(LeaveRequest $leaveRequest, int $approvedBy, ?string $catatan = null): LeaveRequest
    {
        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'catatan_approval' => $catatan,
        ]);

        return $leaveRequest;
    }

    public function cancelLeaveRequest(LeaveRequest $leaveRequest, int $cancelledBy): LeaveRequest
    {
        if ($leaveRequest->status !== 'pending') {
            throw new \RuntimeException('Pengajuan yang sudah diproses tidak dapat dibatalkan.');
        }

        $leaveRequest->update([
            'status' => 'cancelled',
            'approved_by' => $cancelledBy,
            'catatan_approval' => 'Dibatalkan oleh pengaju',
        ]);

        return $leaveRequest;
    }
}
