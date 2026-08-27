<x-app-layout>
@section('title', 'Rekap Absensi Karyawan')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Rekap Absensi Karyawan</h2>
</x-slot>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
    <div class="flex gap-4 border-b border-slate-100 mb-4 pb-2">
        <a href="{{ route('attendance.admin', ['view' => 'daily', 'date' => $date]) }}" class="px-4 py-2 text-sm font-medium border-b-2 {{ $viewType === 'daily' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Rekap Harian</a>
        <a href="{{ route('attendance.admin', ['view' => 'monthly', 'month' => $month, 'year' => $year]) }}" class="px-4 py-2 text-sm font-medium border-b-2 {{ $viewType === 'monthly' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Rekap Bulanan</a>
    </div>

    @if($viewType === 'daily')
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="daily">
        <div>
            <label class="text-xs font-medium text-slate-500">Tanggal</label>
            <input type="date" name="date" value="{{ $date }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium">Lihat</button>
    </form>
    @else
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="view" value="monthly">
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
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium">Lihat</button>
    </form>
    @endif
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    @if($viewType === 'daily')
    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="font-semibold text-slate-700">Data Absensi: {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h3>
    </div>
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @foreach($employees as $emp)
            @php
                $att = $attendances->firstWhere('employee_id', $emp->id);
            @endphp
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-700 truncate">{{ $emp->nama }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $emp->jabatan }}</p>
                    </div>
                    @if($att)
                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold
                        {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' :
                           ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ $att->status_label }}
                    </span>
                    @else
                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Belum Ada Data</span>
                    @endif
                </div>
                @if($att)
                <div class="flex items-center gap-4 text-sm font-mono">
                    <span class="text-slate-500">In: <span class="text-slate-700">{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</span></span>
                    <span class="text-slate-500">Out: <span class="text-slate-700">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</span></span>
                    @if($att->shift)
                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $att->shift->name }}</span>
                    @endif
                </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-white border-b border-slate-100">
            <tr>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Karyawan</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Jabatan</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Shift</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Clock In</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Clock Out</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $emp)
                @php
                    $att = $attendances->firstWhere('employee_id', $emp->id);
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4 font-medium text-slate-700">{{ $emp->nama }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $emp->jabatan }}</td>
                    @if($att)
                        <td class="py-3 px-4 text-center">
                            @if($att->shift)
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $att->shift->name }}</span>
                            @else
                                <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono">{{ $att->clock_in ? \Carbon\Carbon::parse($att->clock_in)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-center font-mono">{{ $att->clock_out ? \Carbon\Carbon::parse($att->clock_out)->format('H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $att->status === 'hadir' ? 'bg-emerald-100 text-emerald-700' :
                                   ($att->status === 'alpha' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $att->status_label }}
                            </span>
                        </td>
                    @else
                        <td class="py-3 px-4 text-center text-slate-400 text-xs">-</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-400">-</td>
                        <td class="py-3 px-4 text-center font-mono text-slate-400">-</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Belum Ada Data</span>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <!-- Monthly Summary -->
    <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="font-semibold text-slate-700">Rekap Bulanan: {{ date('F', mktime(0, 0, 0, $month, 10)) }} {{ $year }}</h3>
    </div>
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @foreach($employees as $emp)
            @php
                $empAtt = $attendances->where('employee_id', $emp->id);
                $summary = [
                    'hadir' => $empAtt->where('status', 'hadir')->count(),
                    'sakit' => $empAtt->where('status', 'sakit')->count(),
                    'izin' => $empAtt->where('status', 'izin')->count(),
                    'cuti' => $empAtt->where('status', 'cuti')->count(),
                    'alpha' => $empAtt->where('status', 'alpha')->count(),
                ];
            @endphp
            <div class="p-4">
                <p class="font-medium text-slate-700 truncate">{{ $emp->nama }}</p>
                <p class="text-xs text-slate-400 mt-0.5 mb-2">{{ $emp->jabatan }}</p>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                    <span class="font-bold text-emerald-600">Hadir {{ $summary['hadir'] }}</span>
                    <span class="font-semibold text-amber-500">Sakit {{ $summary['sakit'] }}</span>
                    <span class="font-semibold text-indigo-500">Izin {{ $summary['izin'] }}</span>
                    <span class="font-semibold text-purple-500">Cuti {{ $summary['cuti'] }}</span>
                    <span class="font-bold text-red-500">Alpha {{ $summary['alpha'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-white border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600 w-64">Karyawan</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Hadir</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Sakit</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Izin</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Cuti</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Alpha</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $emp)
                    @php
                        $empAtt = $attendances->where('employee_id', $emp->id);
                        $summary = [
                            'hadir' => $empAtt->where('status', 'hadir')->count(),
                            'sakit' => $empAtt->where('status', 'sakit')->count(),
                            'izin' => $empAtt->where('status', 'izin')->count(),
                            'cuti' => $empAtt->where('status', 'cuti')->count(),
                            'alpha' => $empAtt->where('status', 'alpha')->count(),
                        ];
                    @endphp
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-700 block">{{ $emp->nama }}</span>
                            <span class="text-xs text-slate-400">{{ $emp->jabatan }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-emerald-600">{{ $summary['hadir'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-amber-500">{{ $summary['sakit'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-indigo-500">{{ $summary['izin'] }}</td>
                        <td class="py-3 px-4 text-center font-semibold text-purple-500">{{ $summary['cuti'] }}</td>
                        <td class="py-3 px-4 text-center font-bold text-red-500">{{ $summary['alpha'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <div class="px-4 py-3 border-t border-slate-100">{{ $employees->appends(request()->query())->links() }}</div>
</div>
</x-app-layout>
