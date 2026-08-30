@if(auth()->user()->hasRole(['admin']))
    @include('leave-requests.partials.approve-modal', ['req' => $req])
@elseif($req->status === 'pending')
    <form method="POST" action="{{ route('leave-requests.cancel', $req) }}" onsubmit="return confirmForm(this, 'Yakin ingin membatalkan pengajuan {{ strtolower($req->jenis_label) }} ini?', { title: 'Batalkan Pengajuan', confirmText: 'Ya, Batalkan', danger: true })">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 border border-red-200 hover:bg-red-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            Batalkan
        </button>
    </form>
@else
    <span class="text-slate-300">—</span>
@endif