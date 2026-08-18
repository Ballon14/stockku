<x-app-layout>
@section('title', 'Dashboard Karyawan')

<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
</x-slot>

<x-download-apk-banner />

<div class="max-w-2xl mx-auto">
    <!-- Status Absensi Hari Ini -->
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 text-center mb-6">
        <div class="w-20 h-20 mx-auto mb-4 rounded-2xl flex items-center justify-center {{ $todayAttendance && $todayAttendance->clock_in ? 'bg-emerald-100' : 'bg-slate-100' }}">
            <svg class="w-10 h-10 {{ $todayAttendance && $todayAttendance->clock_in ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">
            @if($todayAttendance && $todayAttendance->clock_in && $todayAttendance->clock_out)
                Anda sudah selesai hari ini <i class="fa-solid fa-circle-check text-emerald-500 ms-1"></i>
            @elseif($todayAttendance && $todayAttendance->clock_in)
                Anda sudah Clock-In <i class="fa-regular fa-clock text-indigo-500 ms-1"></i>
            @else
                Belum Clock-In Hari Ini
            @endif
        </h3>
        @if($todayAttendance)
        <div class="flex justify-center gap-8 mt-4 text-sm">
            <div>
                <span class="text-slate-500">Clock-In:</span>
                <span class="font-semibold text-slate-700">{{ $todayAttendance->clock_in ? \Carbon\Carbon::parse($todayAttendance->clock_in)->format('H:i') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-500">Clock-Out:</span>
                <span class="font-semibold text-slate-700">{{ $todayAttendance->clock_out ? \Carbon\Carbon::parse($todayAttendance->clock_out)->format('H:i') : '-' }}</span>
            </div>
        </div>
        @endif
        <div class="mt-6">
            <a href="{{ route('attendance.clock') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Halaman Absensi
            </a>
        </div>
    </div>

    <!-- Riwayat Absensi Terakhir -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Riwayat Absensi Terakhir</h3>
        <div class="space-y-3">
            @forelse($recentAttendances as $att)
            <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-slate-700">{{ $att->tanggal->translatedFormat('l, d M Y') }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }} -
                        {{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                    {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' :
                       ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $att->status_label }}
                </span>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Belum ada riwayat absensi</p>
            @endforelse
        </div>
    </div>
</div>
</x-app-layout>
