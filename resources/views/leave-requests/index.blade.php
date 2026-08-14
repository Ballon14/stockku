<x-app-layout>
@section('title', 'Pengajuan Izin/Cuti')
<x-slot name="header">
    <div class="flex items-center justify-between">
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
                    @if($req->status === 'pending')
                    <div class="flex items-center justify-center gap-2" x-data="{ open: false }">
                        <button @click="open = true" class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition-colors">Proses</button>

                        <!-- Modal Proses Approval -->
                        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" style="display: none;">
                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-left" @click.away="open = false">
                                <h3 class="text-lg font-bold text-slate-800 mb-4">Proses Pengajuan {{ $req->employee->nama }}</h3>
                                <div class="mb-4 text-sm text-slate-600">
                                    <p><strong>Jenis:</strong> <span class="uppercase">{{ $req->jenis }}</span></p>
                                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($req->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($req->tanggal_selesai)->format('d/m/Y') }}</p>
                                    <p class="mt-2 text-slate-500 bg-slate-50 p-2 rounded-lg italic">"{{ $req->keterangan }}"</p>
                                </div>
                                <hr class="my-4 border-slate-100">
                                <form method="POST" action="{{ route('leave-requests.approve', $req) }}" id="form-approve-{{ $req->id }}">@csrf</form>
                                <form method="POST" action="{{ route('leave-requests.reject', $req) }}" id="form-reject-{{ $req->id }}">@csrf</form>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Admin (Opsional)</label>
                                    <input type="text" name="catatan_approval" form="form-approve-{{ $req->id }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500" placeholder="Catatan approval..." onchange="document.getElementById('reject-note-{{ $req->id }}').value = this.value">
                                    <input type="hidden" name="catatan_approval" id="reject-note-{{ $req->id }}" form="form-reject-{{ $req->id }}">
                                </div>

                                <div class="flex gap-3">
                                    <button type="button" @click="open = false" class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Batal</button>
                                    <button type="submit" form="form-reject-{{ $req->id }}" class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">Tolak</button>
                                    <button type="submit" form="form-approve-{{ $req->id }}" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-colors">Setujui</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <span class="text-xs text-slate-400">Diproses oleh: {{ $req->approvedBy->name ?? '-' }}</span>
                    @endif
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
    <div class="px-4 py-3 border-t border-slate-100">{{ $leaveRequests->links() }}</div>
</div>
</x-app-layout>
