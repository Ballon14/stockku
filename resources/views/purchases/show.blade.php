<x-app-layout>
@section('title', 'Detail Pembelian')
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-black text-slate-800 tracking-tight">Detail Pembelian</h2>
        <a href="{{ route('purchases.index') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>
</x-slot>

<div class="max-w-5xl mx-auto pb-10">
    {{-- Header Card --}}
    <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-xl shadow-slate-200/40 border border-white overflow-hidden mb-8 relative">
        
        {{-- Invoice badge header --}}
        <div class="relative bg-gradient-to-br from-indigo-600 via-purple-600 to-indigo-900 px-8 py-8 overflow-hidden">
            <!-- Decorative Blobs -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="absolute bottom-0 left-10 -mb-10 w-32 h-32 bg-purple-400 opacity-20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-inner">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="text-indigo-100 text-sm font-medium tracking-wide uppercase">Invoice Pembelian</p>
                        <p class="text-white font-black font-mono text-2xl tracking-wider mt-0.5">{{ $purchase->invoice_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-bold bg-emerald-400/20 text-emerald-50 border border-emerald-400/30 backdrop-blur-md shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        Diterima
                    </span>
                </div>
            </div>
        </div>

        {{-- Info grid --}}
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Info Item 1 -->
                <div class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:bg-indigo-100 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0 pt-0.5">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="text-base font-bold text-slate-800">{{ \Carbon\Carbon::parse($purchase->tanggal)->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
                <!-- Info Item 2 -->
                <div class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-purple-100 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:bg-purple-100 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0 pt-0.5">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Supplier</p>
                        <p class="text-base font-bold text-slate-800 truncate">{{ $purchase->supplier->name }}</p>
                    </div>
                </div>
                <!-- Info Item 3 -->
                <div class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-teal-100 transition-all duration-300">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:bg-teal-100 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0 pt-0.5">
                        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Dicatat Oleh</p>
                        <p class="text-base font-bold text-slate-800 truncate">{{ $purchase->user->name }}</p>
                    </div>
                </div>
            </div>

            @if($purchase->keterangan)
            <div class="mb-8 relative overflow-hidden rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-100/50 p-6 shadow-sm">
                <div class="absolute -right-6 -top-6 text-amber-500/10">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                </div>
                <div class="relative z-10 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-900 mb-1">Keterangan / Referensi</h4>
                        <p class="text-sm text-amber-800 leading-relaxed">{{ $purchase->keterangan }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($purchase->foto_nota)
            <div>
                <h4 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Foto Nota / Bukti Pembelian
                </h4>
                <div class="inline-block relative group">
                    <a href="{{ asset('storage/' . $purchase->foto_nota) }}" target="_blank" class="block rounded-2xl overflow-hidden border-2 border-slate-100 hover:border-indigo-400 shadow-sm transition-all duration-300">
                        <img src="{{ asset('storage/' . $purchase->foto_nota) }}" alt="Foto Nota" class="w-56 h-auto object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-indigo-900/0 group-hover:bg-indigo-900/10 transition-colors duration-300 flex items-center justify-center pointer-events-none">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-white/90 backdrop-blur text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-lg shadow-sm">Lihat Penuh</span>
                        </div>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 overflow-hidden mb-8">
        <div class="px-8 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                Item Produk
            </h3>
            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-xl border border-indigo-100">{{ $purchase->items->count() }} item</span>
        </div>

        {{-- Mobile: card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($purchase->items as $item)
            <div class="p-5 hover:bg-slate-50 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 truncate">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                        @if($item->product)
                        <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->product->sku }}</p>
                        @endif
                    </div>
                    <p class="text-sm font-black text-indigo-600 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-600 bg-slate-50 px-3 py-2 rounded-lg border border-slate-100 inline-block w-fit">
                    <span class="font-bold text-slate-800">{{ $item->qty }}</span> <span class="text-slate-400">×</span> <span>Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 flex items-center justify-between border-t border-indigo-100">
                <span class="text-sm font-bold text-indigo-900 uppercase tracking-wide">Total Pembelian</span>
                <span class="text-2xl font-black text-indigo-700">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-4 px-8 text-xs font-bold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="text-center py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-28">Qty</th>
                        <th class="text-right py-4 px-6 text-xs font-bold text-slate-500 uppercase tracking-wider w-48">Harga Satuan</th>
                        <th class="text-right py-4 px-8 text-xs font-bold text-slate-500 uppercase tracking-wider w-48">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($purchase->items as $item)
                    <tr class="hover:bg-indigo-50/30 transition-colors duration-200 group">
                        <td class="py-4 px-8">
                            <p class="font-bold text-slate-700 group-hover:text-indigo-700 transition-colors">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                            @if($item->product)
                            <p class="text-xs text-slate-400 font-mono mt-1">{{ $item->product->sku }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-sm group-hover:bg-indigo-100 group-hover:text-indigo-700 transition-colors">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-4 px-8 text-right font-black text-slate-800 group-hover:text-indigo-700 transition-colors">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-8 py-6 bg-gradient-to-r from-slate-50 via-indigo-50/30 to-purple-50/30 border-t border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="text-sm font-bold text-slate-600 uppercase tracking-wider">Total Keseluruhan</span>
                </div>
                <span class="text-2xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    
    <div class="sm:hidden mb-6 flex justify-center">
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center justify-center gap-2 w-full px-4 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-bold hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pembelian
        </a>
    </div>
</div>
</x-app-layout>
