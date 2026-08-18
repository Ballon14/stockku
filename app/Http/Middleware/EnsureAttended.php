<?php

namespace App\Http\Middleware;

use App\Support\AttendanceGate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAttended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->employee) {
            return $next($request);
        }

        // Owner (admin) bebas tanpa pemblokiran
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && (str_starts_with($routeName, 'attendance.') || $routeName === 'logout')) {
            return $next($request);
        }

        if (AttendanceGate::isAttended($user)) {
            return $next($request);
        }

        // Aksi tulis tetap diblokir sampai clock-in
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            $attendance = $user->employee->attendances()
                ->whereDate('tanggal', now()->toDateString())
                ->first();

            $message = $attendance && $attendance->clock_out
                ? 'Anda sudah clock-out hari ini. Aksi ini hanya dapat dilakukan setelah clock-in kembali besok.'
                : 'Anda wajib clock-in terlebih dahulu untuk melakukan aksi ini.';

            return redirect()->route('attendance.clock')->with('warning', $message);
        }

        // Mode baca: akses halaman diizinkan dengan penanda mode baca
        view()->share('attendanceReadOnly', true);

        return $next($request);
    }
}
