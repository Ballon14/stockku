<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->employee) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && (str_starts_with($routeName, 'attendance.') || $routeName === 'logout')) {
            return $next($request);
        }

        $attendance = Attendance::where('employee_id', $user->employee->id)
            ->whereDate('tanggal', Carbon::today())
            ->first();

        $onLeave = $attendance && in_array($attendance->status, ['izin', 'sakit', 'cuti'], true) && ! $attendance->clock_in;
        $worked = $attendance && $attendance->clock_in && ! $attendance->clock_out;

        if (! $onLeave && ! $worked) {
            $message = $attendance && $attendance->clock_out
                ? 'Anda sudah clock-out hari ini, tidak dapat melakukan aktivitas lain. Silakan clock-in kembali besok.'
                : 'Anda wajib clock-in terlebih dahulu sebelum dapat melakukan aktivitas.';

            return redirect()->route('attendance.clock')->with('warning', $message);
        }

        return $next($request);
    }
}
