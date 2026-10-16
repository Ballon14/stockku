<x-app-layout>
@section('title', 'Detail Transaksi')

<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Detail Transaksi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rincian lengkap penjualan & pembayaran</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('sales.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:brightness-110 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Struk
            </a>

            @if(in_array($sale->status, ['completed', 'partial_return']) && $sale->items->contains(fn($i) => ($i->qty - $i->returned_qty) > 0))
            <a href="{{ route('sale-returns.create', $sale) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-rose-600 to-amber-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 hover:brightness-110 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                </svg>
                Retur Barang
            </a>
            @endif
        </div>
    </div>
</x-slot>

<div class="w-full space-y-6">

    {{-- Top Header Banner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Gradient banner --}}
        <div class="bg-gradient-to-r from-indigo-600 via-indigo-700 to-purple-600 px-6 sm:px-8 py-5 text-white">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wider">Invoice Penjualan</p>
                        <p class="text-white font-bold font-mono text-xl sm:text-2xl mt-0.5 tracking-tight">{{ $sale->invoice_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    @if($sale->status === 'completed')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30 backdrop-blur-sm shadow-sm">✓ Selesai</span>
                    @elseif($sale->status === 'partial_return')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-100 border border-amber-400/30 backdrop-blur-sm shadow-sm">⟲ Retur Sebagian</span>
                    @elseif($sale->status === 'returned')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-400/20 text-rose-100 border border-rose-400/30 backdrop-blur-sm shadow-sm">⟲ Diretur Penuh</span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30 backdrop-blur-sm shadow-sm">{{ $sale->status }}</span>
                    @endif

                    @if($sale->payment_method === 'qris')
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-indigo-400/20 text-indigo-100 border border-indigo-400/30 backdrop-blur-sm shadow-sm">QRIS</span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-white/15 text-white/90 border border-white/25 backdrop-blur-sm shadow-sm">Tunai</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info grid across full width --}}
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 {{ $sale->catatan ? 'mb-6' : '' }}">
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tanggal Transaksi</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $sale->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-slate-500">{{ $sale->created_at->format('H:i') }} WIB</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-purple-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Kasir</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 break-words">{{ $sale->user->name }}</p>
                        <p class="text-xs text-slate-500">Petugas Kasir</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Total Belanja</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $sale->items->count() }} Jenis Produk</p>
                        <p class="text-xs text-slate-500">{{ $sale->items->sum('qty') }} unit barang</p>
                    </div>
                </div>

                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-teal-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Metode Bayar</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $sale->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</p>
                        <p class="text-xs text-emerald-600 font-medium">Lunas</p>
                    </div>
                </div>
            </div>

            @if($sale->catatan)
            <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-800 block mb-0.5">Catatan Transaksi</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $sale->catatan }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Rincian Item Produk
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-3 py-1 rounded-lg">{{ $sale->items->count() }} jenis produk</span>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($sale->items as $item)
            <div class="p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 break-words">{{ $item->product->name }}</p>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                    </div>
                    <p class="text-sm font-bold text-indigo-600 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 bg-slate-50 px-3 py-1.5 rounded-lg w-fit">
                    <span><strong class="text-slate-800">{{ $item->qty }}</strong> × Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                    @if($item->diskon > 0)
                    <span class="text-rose-500 font-medium">· Diskon: -Rp {{ number_format($item->diskon, 0, ',', '.') }}</span>
                    @endif
                    @if($item->returned_qty > 0)
                    <span class="text-amber-600 font-medium">· Diretur: {{ $item->returned_qty }} unit</span>
                    @endif
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
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Qty</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Harga Satuan</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-36">Diskon</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-48">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800 text-sm leading-snug">{{ $item->product->name }}</p>
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                            @if($item->returned_qty > 0)
                            <span class="inline-block mt-1 text-xs bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full font-medium">
                                ⟲ Diretur: {{ $item->returned_qty }} unit
                            </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-sm border border-indigo-100">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-right">
                            @if($item->diskon > 0)
                            <span class="text-rose-500 font-medium">-Rp {{ number_format($item->diskon, 0, ',', '.') }}</span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-800 text-base">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Payment summary section --}}
        <div class="px-6 py-6 bg-slate-50/80 border-t border-slate-100">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="space-y-1 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium text-slate-700">Status Pembayaran: <strong class="text-emerald-600">Lunas</strong></span>
                    </div>
                    <p class="text-slate-400 pl-6">Metode: {{ $sale->payment_method === 'qris' ? 'QRIS Digital' : 'Uang Tunai (Cash)' }}</p>
                </div>

                <div class="w-full sm:w-96 space-y-2.5 text-sm bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Subtotal Produk</span>
                        <span class="font-medium text-slate-800">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->diskon > 0)
                    <div class="flex justify-between items-center text-rose-600">
                        <span>Diskon Transaksi</span>
                        <span class="font-medium">- Rp {{ number_format($sale->diskon, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center text-base font-bold text-slate-900 pt-2.5 border-t border-dashed border-slate-200">
                        <span>Grand Total</span>
                        <span class="text-indigo-700 text-lg font-extrabold">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600 pt-1">
                        <span>Bayar ({{ $sale->payment_method === 'qris' ? 'QRIS' : 'Tunai' }})</span>
                        <span class="font-medium text-slate-800">Rp {{ number_format($sale->bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-emerald-600 font-semibold pt-1 border-t border-slate-100">
                        <span>Kembalian</span>
                        <span class="text-base font-bold">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sale Returns History --}}
    @if($sale->returns && $sale->returns->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-amber-50/40">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                </svg>
                Riwayat Retur Penjualan
            </h3>
            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-3 py-1 rounded-lg">{{ $sale->returns->count() }} kali retur</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600 w-44">No. Retur</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600 w-40">Tanggal</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Alasan</th>
                        <th class="text-right py-3.5 px-4 font-semibold text-slate-600 w-36">Total Refund</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600 w-40">Diproses Oleh</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->returns as $ret)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6 font-mono text-xs font-bold text-amber-600">{{ $ret->return_number }}</td>
                        <td class="py-4 px-4 text-slate-600 text-xs">{{ $ret->created_at->format('d/m/Y H:i') }} WIB</td>
                        <td class="py-4 px-4 text-slate-600">{{ $ret->alasan }}</td>
                        <td class="py-4 px-4 text-right font-bold text-rose-600">Rp {{ number_format($ret->total_refund, 0, ',', '.') }}</td>
                        <td class="py-4 px-4 text-slate-600">{{ $ret->processedBy ? $ret->processedBy->name : '-' }}</td>
                        <td class="py-4 px-4 text-center">
                            <a href="{{ route('sale-returns.show', $ret) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
</x-app-layout>
