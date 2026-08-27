<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AttendanceService
{
    public function clockIn(Employee $employee, ?int $shiftId = null): Attendance
    {
        $today = Carbon::today()->toDateString();
        $now = Carbon::now()->toTimeString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (! $attendance) {
            return Attendance::create([
                'employee_id' => $employee->id,
                'shift_id' => $shiftId,
                'tanggal' => $today,
                'clock_in' => $now,
                'status' => 'hadir',
            ]);
        }

        // Jangan timpa status izin/sakit/cuti yang sudah disetujui
        if (! $attendance->clock_in) {
            $attendance->update([
                'clock_in' => $now,
                'shift_id' => $shiftId ?? $attendance->shift_id,
            ]);
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
        $query = Attendance::with('shift')->where('employee_id', $employee->id)
            ->orderBy('tanggal', 'desc');

        if ($month && $year) {
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
            $query->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end);
        }

        return $query->paginate(31);
    }

    public function getAllAttendances($date = null, $month = null, $year = null)
    {
        $query = Attendance::with(['employee', 'shift'])->orderBy('tanggal', 'desc');

        if ($date) {
            $query->where('tanggal', $date);
        } elseif ($month && $year) {
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
            $query->whereDate('tanggal', '>=', $start)->whereDate('tanggal', '<=', $end);
        }

        return $query->get();
    }

    public function getTodaySummary(): array
    {
        $today = Carbon::today()->toDateString();
        $totalEmployees = Employee::where('is_active', true)->count();
        $hadir = Attendance::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $berizin = LeaveRequest::where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->count();
        $tidakHadir = max(0, $totalEmployees - $hadir - $berizin);

        return [
            'total' => $totalEmployees,
            'hadir' => $hadir,
            'berizin' => $berizin,
            'tidak_hadir' => $tidakHadir,
        ];
    }

    public function createLeaveRequest(Employee $employee, array $data): LeaveRequest
    {
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where('tanggal_mulai', '<=', $data['tanggal_selesai'])
            ->where('tanggal_selesai', '>=', $data['tanggal_mulai'])
            ->exists();

        if ($overlap) {
            throw new \RuntimeException('Anda sudah memiliki pengajuan yang berjalan di rentang tanggal tersebut.');
        }

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
        if ($leaveRequest->status !== 'pending') {
            throw new \RuntimeException('Hanya pengajuan berstatus pending yang dapat disetujui.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'catatan_approval' => $catatan,
        ]);

        // Create attendance records for approved leave
        $start = Carbon::parse($leaveRequest->tanggal_mulai)->copy();
        $end = Carbon::parse($leaveRequest->tanggal_selesai)->copy();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $exists = Attendance::where('employee_id', $leaveRequest->employee_id)
                ->whereDate('tanggal', $date->toDateString())
                ->exists();

            if (! $exists) {
                Attendance::create([
                    'employee_id' => $leaveRequest->employee_id,
                    'tanggal' => $date->toDateString(),
                    'status' => $leaveRequest->jenis,
                    'keterangan' => $leaveRequest->keterangan,
                ]);
            }
        }

        return $leaveRequest;
    }

    public function rejectLeaveRequest(LeaveRequest $leaveRequest, int $approvedBy, ?string $catatan = null): LeaveRequest
    {
        if (! in_array($leaveRequest->status, ['pending', 'approved'], true)) {
            throw new \RuntimeException('Pengajuan ini tidak dapat ditolak.');
        }

        $wasApproved = $leaveRequest->status === 'approved';

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'catatan_approval' => $catatan,
        ]);

        // Batalkan catatan absensi yang dibuat saat persetujuan sebelumnya
        if ($wasApproved) {
            Attendance::where('employee_id', $leaveRequest->employee_id)
                ->whereDate('tanggal', '>=', $leaveRequest->tanggal_mulai)
                ->whereDate('tanggal', '<=', $leaveRequest->tanggal_selesai)
                ->where('status', $leaveRequest->jenis)
                ->whereNull('clock_in')
                ->whereNull('clock_out')
                ->delete();
        }

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
