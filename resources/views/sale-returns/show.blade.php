<x-app-layout>
@section('title', 'Detail Retur Penjualan')

<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Detail Retur Penjualan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rincian barang yang dikembalikan pelanggan</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('sales.show', $saleReturn->sale_id) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Lihat Transaksi
            </a>
            <a href="{{ route('sale-returns.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>
</x-slot>

<div class="max-w-4xl">

    {{-- Header Banner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        {{-- Gradient banner --}}
        <div class="bg-gradient-to-r from-amber-600 to-rose-600 px-6 py-5 text-white">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wider">Bukti Retur Penjualan</p>
                        <p class="text-white font-bold font-mono text-xl mt-0.5">{{ $saleReturn->return_number }}</p>
                    </div>
                </div>
                <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-white/20 border border-white/30 backdrop-blur-sm text-white">
                    ✓ Selesai Diproses
                </span>
            </div>
        </div>

        {{-- Meta info grid --}}
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-amber-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tanggal Retur</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $saleReturn->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $saleReturn->created_at->format('H:i') }} WIB</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Invoice Asli</p>
                        <a href="{{ route('sales.show', $saleReturn->sale_id) }}" class="font-mono text-sm font-bold text-indigo-600 hover:underline mt-0.5 block truncate">{{ $saleReturn->sale->invoice_number }}</a>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-purple-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Diproses Oleh</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 truncate">{{ $saleReturn->processedBy ? $saleReturn->processedBy->name : '-' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-9 h-9 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Jumlah Item</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $saleReturn->items->count() }} produk</p>
                    </div>
                </div>
            </div>

            @if($saleReturn->alasan)
            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-900 block mb-0.5">Alasan Retur</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $saleReturn->alasan }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Item Barang yang Diretur
            </h3>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">{{ $saleReturn->items->count() }} item</span>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($saleReturn->items as $item)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $item->product->name }}</p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                    </div>
                    <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold text-sm border border-rose-100">
                        {{ $item->qty }} unit
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">Produk</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">SKU</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-32">Qty Diretur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($saleReturn->items as $item)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800">{{ $item->product->name }}</p>
                        </td>
                        <td class="py-4 px-4">
                            <span class="font-mono text-xs text-slate-400">{{ $item->product->sku }}</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold text-sm border border-rose-100">
                                {{ $item->qty }} unit
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile bottom nav --}}
    <div class="sm:hidden pb-6">
        <a href="{{ route('sale-returns.index') }}"
            class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold shadow-sm">
            ← Kembali ke Daftar Retur
        </a>
    </div>
</div>
</x-app-layout>
