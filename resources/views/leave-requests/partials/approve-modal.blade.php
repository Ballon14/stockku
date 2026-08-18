@if($req->status === 'pending')
<div class="flex items-center justify-center gap-2" x-data="{ open: false }">
    <button @click="open = true" class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-semibold hover:bg-indigo-100 transition-colors">Proses</button>

    <!-- Modal Proses Approval -->
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-left mx-4" @click.away="open = false">
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
                <label class="block text-sm font-medium text-slate-700 mb-1">Catatan Approval (Opsional)</label>
                <input type="text" name="catatan_approval" form="form-approve-{{ $req->id }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500" placeholder="Catatan approval..." onchange="document.getElementById('reject-note-{{ $req->id }}').value = this.value">
                <input type="hidden" name="catatan_approval" id="reject-note-{{ $req->id }}" form="form-reject-{{ $req->id }}">
            </div>

            <div class="flex gap-3">
                <button type="button" @click="open = false" class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-200 transition-colors">Batal</button>
                <button type="submit" form="form-reject-{{ $req->id }}" onclick="return confirmEvent(event, 'Yakin menolak pengajuan {{ $req->employee->nama }} ini? Tindakan ini tidak dapat dibatalkan.', { danger: true, title: 'Tolak Pengajuan', confirmText: 'Ya, Tolak' })" class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">Tolak</button>
                <button type="submit" form="form-approve-{{ $req->id }}" class="flex-1 px-4 py-2 bg-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-colors">Setujui</button>
            </div>
        </div>
    </div>
</div>
@else
<span class="text-xs text-slate-400">Diproses oleh: {{ $req->approvedBy->name ?? '-' }}</span>
@endif