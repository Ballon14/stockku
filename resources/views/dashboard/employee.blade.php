<x-app-layout>
@section('title', 'Dashboard Karyawan')

<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
</x-slot>

<x-download-apk-banner />

<div class="max-w-2xl mx-auto">
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
