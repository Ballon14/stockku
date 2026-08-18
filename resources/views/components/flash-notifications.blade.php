@php
    $flashes = collect();
    foreach (['success', 'error', 'warning'] as $type) {
        if (session($type)) {
            $flashes->push(['type' => $type, 'message' => session($type)]);
        }
    }
@endphp

@if($flashes->isNotEmpty())
<div class="fixed left-1/2 top-5 z-[60] w-full max-w-md -translate-x-1/2 px-4 space-y-3 pointer-events-none">
    @foreach($flashes as $flash)
    <div class="flash-toast pointer-events-auto flex items-start gap-3 rounded-xl p-4 shadow-xl shadow-slate-900/10 border transition-all duration-300 {{ $flash['type'] === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($flash['type'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-red-50 border-red-200 text-red-800') }}">
        <span class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center {{ $flash['type'] === 'success' ? 'bg-emerald-500' : ($flash['type'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}">
            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                @if($flash['type'] === 'success')
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                @elseif($flash['type'] === 'warning')
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                @endif
            </svg>
        </span>
        <p class="text-sm font-medium leading-snug flex-1 min-w-0">{{ $flash['message'] }}</p>
        <button type="button" class="shrink-0 p-1 rounded-md opacity-60 hover:opacity-100 transition-opacity" aria-label="Tutup" onclick="this.closest('.flash-toast').remove()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endforeach
</div>

<script>
    document.querySelectorAll('.flash-toast').forEach(function (toast) {
        setTimeout(function () {
            toast.classList.add('opacity-0', '-translate-y-2');
            setTimeout(function () { toast.remove(); }, 300);
        }, 3000);
    });
</script>
@endif