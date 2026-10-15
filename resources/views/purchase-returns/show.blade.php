<x-app-layout>
@section('title', 'Detail Retur Pembelian')
<x-slot name="header">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-returns.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="text-2xl font-bold text-slate-800">Detail Retur Pembelian</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.show', $purchaseReturn->purchase_id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Lihat Pembelian
            </a>
            <a href="{{ route('purchase-returns.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors shadow-sm">
                Daftar Retur
            </a>
        </div>
    </div>
</x-slot>

<div class="max-w-5xl">
    {{-- Header Banner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-rose-600 to-amber-600 px-6 py-5 text-white">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-rose-100 text-xs font-semibold uppercase tracking-wider">Bukti Retur Pembelian ke Supplier</p>
                        <p class="text-white font-bold font-mono text-xl mt-0.5">{{ $purchaseReturn->return_number }}</p>
                    </div>
                </div>
                <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-white/20 border border-white/30 backdrop-blur-sm text-white">
                    ✓ Selesai Diproses
                </span>
            </div>
        </div>

        {{-- Meta info grid --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs text-slate-500 font-medium">Tanggal Retur</p>
                    <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ \Carbon\Carbon::parse($purchaseReturn->tanggal)->translatedFormat('d F Y') }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs text-slate-500 font-medium">Invoice Pembelian</p>
                    <p class="mt-0.5">
                        <a href="{{ route('purchases.show', $purchaseReturn->purchase_id) }}" class="font-mono text-sm font-bold text-indigo-600 hover:underline">
                            {{ $purchaseReturn->purchase->invoice_number }}
                        </a>
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs text-slate-500 font-medium">Supplier</p>
                    <p class="text-sm font-semibold text-slate-800 mt-0.5 truncate">{{ $purchaseReturn->supplier->name }}</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <p class="text-xs text-slate-500 font-medium">Diproses Oleh</p>
                    <p class="text-sm font-semibold text-slate-800 mt-0.5 truncate">{{ $purchaseReturn->processedBy ? $purchaseReturn->processedBy->name : '-' }}</p>
                </div>
            </div>

            @if($purchaseReturn->alasan)
            <div class="p-4 rounded-xl bg-rose-50/60 border border-rose-100 flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-900 block mb-0.5">Alasan Retur</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $purchaseReturn->alasan }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Item Barang yang Diretur
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                {{ $purchaseReturn->items->count() }} item
            </span>
        </div>

        {{-- Mobile: card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($purchaseReturn->items as $item)
            <div class="p-4 bg-white">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                        @if($item->product)
                        <p class="text-xs text-slate-500 font-mono">{{ $item->product->sku }}</p>
                        @endif
                    </div>
                    <p class="text-sm font-bold text-rose-600 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-600 bg-slate-50 px-2 py-1 rounded-lg w-fit">
                    <span class="font-bold text-slate-800">{{ $item->qty }} unit</span>
                    <span>× Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600">Total Nilai Retur</span>
                <span class="text-xl font-bold text-rose-600">Rp {{ number_format($purchaseReturn->total_nilai, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">Produk</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Qty Diretur</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Harga Beli</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-48">Subtotal Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($purchaseReturn->items as $item)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                            @if($item->product)
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold text-sm border border-rose-100">
                                {{ $item->qty }} unit
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-800">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Nilai Retur ke Supplier</span>
                <span class="text-2xl font-bold text-rose-600">Rp {{ number_format($purchaseReturn->total_nilai, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
