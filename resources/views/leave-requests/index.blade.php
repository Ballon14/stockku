<x-app-layout>
@section('title', 'Pengajuan Izin/Cuti')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-slate-800">Pengajuan Izin / Cuti</h2>
        @if(!auth()->user()->hasRole('admin'))
        <a href="{{ route('leave-requests.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buat Pengajuan Baru
        </a>
        @endif
    </div>
</x-slot>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($leaveRequests as $req)
        <div class="p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0">
                    <p class="text-sm text-slate-400">{{ $req->created_at->format('d/m/Y') }}</p>
                    @if(auth()->user()->hasRole('admin'))
                    <p class="font-medium text-slate-700 mt-0.5">{{ $req->employee->nama }}</p>
                    @endif
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">{{ $req->jenis }}</span>
                </div>
                <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold
                    {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                       ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $req->status_label }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ \Carbon\Carbon::parse($req->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($req->tanggal_selesai)->format('d/m/Y') }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ $req->keterangan }}</p>
            @if(auth()->user()->hasRole('admin'))
            <div class="mt-3 flex justify-end">
                @include('leave-requests.partials.approve-modal', ['req' => $req])
            </div>
            @endif
        </div>
        @empty
        <div class="py-8 text-center text-slate-400">Belum ada data pengajuan.</div>
        @endforelse
    </div>

    <!-- Desktop: table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Tanggal Pengajuan</th>
                    @if(auth()->user()->hasRole('admin'))
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Karyawan</th>
                    @endif
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Jenis</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Rentang Waktu</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Keterangan</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Status</th>
                    @if(auth()->user()->hasRole('admin'))
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi (Admin)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $req)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4 text-slate-600">{{ $req->created_at->format('d/m/Y') }}</td>
                    @if(auth()->user()->hasRole('admin'))
                    <td class="py-3 px-4 font-medium text-slate-700">{{ $req->employee->nama }}</td>
                    @endif
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 uppercase">{{ $req->jenis }}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-600">
                        {{ \Carbon\Carbon::parse($req->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($req->tanggal_selesai)->format('d/m/Y') }}
                    </td>
                    <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ $req->keterangan }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            {{ $req->status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                               ($req->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ $req->status_label }}
                        </span>
                    </td>
                    @if(auth()->user()->hasRole('admin'))
                    <td class="py-3 px-4 text-center">
                        @include('leave-requests.partials.approve-modal', ['req' => $req])
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->hasRole('admin') ? 7 : 5 }}" class="py-8 text-center text-slate-400">Belum ada data pengajuan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $leaveRequests->links() }}</div>
</div>
</x-app-layout>
