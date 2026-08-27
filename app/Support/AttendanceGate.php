<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceGate
{
    public static function isAttended(User $user): bool
    {
        if (! $user->employee || $user->hasRole('admin')) {
            return true;
        }

        $attendance = Attendance::where('employee_id', $user->employee->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        if ($attendance && in_array($attendance->status, ['izin', 'sakit', 'cuti'], true) && ! $attendance->clock_in) {
            return true;
        }

        return $attendance && $attendance->clock_in && ! $attendance->clock_out;
    }

    public static function isReadOnly(User $user): bool
    {
        return ! self::isAttended($user);
    }
}
