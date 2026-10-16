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
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Lihat Transaksi Asal
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

<div class="w-full space-y-6">

    {{-- Header Banner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Gradient banner --}}
        <div class="bg-gradient-to-r from-amber-600 via-rose-600 to-rose-700 px-6 sm:px-8 py-5 text-white">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wider">Bukti Retur Penjualan</p>
                        <p class="text-white font-bold font-mono text-xl sm:text-2xl mt-0.5 tracking-tight">{{ $saleReturn->return_number }}</p>
                    </div>
                </div>
                <span class="px-4 py-1.5 rounded-full text-xs sm:text-sm font-bold bg-white/20 border border-white/30 backdrop-blur-sm text-white shadow-sm">
                    ✓ Selesai Diproses
                </span>
            </div>
        </div>

        {{-- Meta info grid across full width --}}
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 {{ $saleReturn->alasan ? 'mb-6' : '' }}">
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-amber-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tanggal Retur</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $saleReturn->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $saleReturn->created_at->format('H:i') }} WIB</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Invoice Asal</p>
                        <a href="{{ route('sales.show', $saleReturn->sale_id) }}" class="font-mono text-sm font-bold text-indigo-600 hover:underline mt-0.5 block break-all">{{ $saleReturn->sale->invoice_number }}</a>
                        <p class="text-xs text-slate-400">Klik untuk rincian</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-purple-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Diproses Oleh</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 break-words">{{ $saleReturn->processedBy ? $saleReturn->processedBy->name : '-' }}</p>
                        <p class="text-xs text-slate-400">Petugas Kasir/Admin</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-rose-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Total Refund</p>
                        <p class="text-sm font-bold text-rose-600 mt-0.5">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400">{{ $saleReturn->items->count() }} jenis produk</p>
                    </div>
                </div>
            </div>

            @if($saleReturn->alasan)
            <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 flex items-start gap-3.5">
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
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Item Barang yang Diretur
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-3 py-1 rounded-lg">{{ $saleReturn->items->count() }} item</span>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($saleReturn->items as $item)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 break-words">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                        @if($item->product)
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold text-sm border border-rose-100">
                        {{ $item->qty }} unit
                    </span>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-600 bg-slate-50 px-3 py-2 rounded-lg">
                    <span>Harga Satuan: Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                    <span class="font-bold text-rose-600">Subtotal: Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600">Total Refund</span>
                <span class="text-xl font-bold text-rose-600">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">Produk</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600 w-44">SKU</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-32">Qty Diretur</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Harga Satuan</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-48">Subtotal Refund</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($saleReturn->items as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800 leading-snug">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                        </td>
                        <td class="py-4 px-4">
                            <span class="font-mono text-xs text-slate-400">{{ $item->product ? $item->product->sku : '-' }}</span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3.5 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold text-sm border border-rose-100">
                                {{ $item->qty }} unit
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-rose-600 text-base">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Nilai Refund ke Pelanggan</span>
                <span class="text-2xl font-bold text-rose-600">Rp {{ number_format($saleReturn->total_refund, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

</div>
</x-app-layout>
