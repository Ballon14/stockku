<x-app-layout>
@section('title', 'Riwayat Absensi')
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Riwayat Absensi Anda</h2>
        <a href="{{ route('attendance.clock') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Halaman Clock In/Out
        </a>
    </div>
</x-slot>

<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs font-medium text-slate-500">Bulan</label>
                <select name="month" class="w-32 mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-slate-500">Tahun</label>
                <select name="year" class="w-24 mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    @for($i=date('Y')-2; $i<=date('Y'); $i++)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">Tampilkan</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
    <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Jam Masuk (Clock In)</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Jam Keluar (Clock Out)</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4 font-medium text-slate-700">{{ $att->tanggal->translatedFormat('l, d F Y') }}</td>
                    <td class="py-3 px-4 text-center font-mono {{ $att->clock_in ? 'text-slate-700' : 'text-slate-400' }}">{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</td>
                    <td class="py-3 px-4 text-center font-mono {{ $att->clock_out ? 'text-slate-700' : 'text-slate-400' }}">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' :
                               ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $att->status_label }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-slate-400">Tidak ada data absensi untuk bulan ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
</x-app-layout>
