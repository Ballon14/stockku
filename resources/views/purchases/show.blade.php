<x-app-layout>
@section('title', 'Detail Pembelian (Barang Masuk)')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Detail Pembelian (Barang Masuk)</h2>
            <p class="text-xs text-slate-500">Pencatatan faktur barang masuk dari supplier</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('supplier-bills.show', $purchase) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-teal-500/20 hover:shadow-teal-500/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Status Tagihan / Pembayaran
            </a>
            @if($purchase->status !== 'returned' && $purchase->items->contains(fn($i) => ($i->qty - $i->returned_qty) > 0))
            <a href="{{ route('purchase-returns.create', $purchase) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-rose-600 to-amber-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                Retur ke Supplier
            </a>
            @endif
            <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>
</x-slot>

<div class="w-full space-y-6">
    {{-- Header Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Invoice badge header --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-5">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div>
                        <p class="text-indigo-100 text-xs font-semibold uppercase tracking-wide">Invoice Pembelian</p>
                        <p class="text-white font-bold font-mono text-xl mt-0.5">{{ $purchase->invoice_number }}</p>
                    </div>
                </div>
                <div>
                    @if($purchase->status === 'returned')
                    <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-rose-400/20 text-rose-100 border border-rose-400/30 backdrop-blur-sm">
                        ⟲ Diretur Penuh
                    </span>
                    @elseif($purchase->status === 'partial_return')
                    <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-amber-400/20 text-amber-100 border border-amber-400/30 backdrop-blur-sm">
                        ⟲ Retur Sebagian
                    </span>
                    @else
                    <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30 backdrop-blur-sm">
                        ✓ Diterima
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info grid --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tanggal Pembelian</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ \Carbon\Carbon::parse($purchase->tanggal)->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-purple-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Supplier</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 truncate">{{ $purchase->supplier->name }}</p>
                        @if($purchase->supplier_invoice)
                        <p class="text-xs text-slate-400 mt-0.5 font-mono">Faktur: {{ $purchase->supplier_invoice }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 hover:border-teal-100 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Dicatat Oleh</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 truncate">{{ $purchase->user->name }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment & Debt summary row --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tipe Pembayaran</p>
                        <p class="text-sm font-bold text-slate-800 mt-0.5">
                            {{ $purchase->payment_type === 'credit' ? '⏳ Tempo / Kredit' : '💵 Tunai Langsung' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-lg {{ $purchase->is_overdue ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Jatuh Tempo</p>
                        <p class="text-sm font-bold {{ $purchase->is_overdue ? 'text-rose-600' : 'text-slate-800' }} mt-0.5">
                            @if($purchase->due_date)
                                {{ \Carbon\Carbon::parse($purchase->due_date)->format('d/m/Y') }}
                                @if($purchase->is_overdue)
                                <span class="text-xs bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-semibold ml-1">Lewat Tempo</span>
                                @endif
                            @else
                                <span class="text-slate-400 font-normal">Tidak Ada</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-4 p-4 rounded-xl {{ $purchase->remaining_bill > 0 ? 'bg-amber-50/70 border-amber-200' : 'bg-emerald-50/70 border-emerald-200' }} border">
                    <div class="w-10 h-10 rounded-lg {{ $purchase->remaining_bill > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs {{ $purchase->remaining_bill > 0 ? 'text-amber-800' : 'text-emerald-800' }} font-medium">Status & Sisa Tagihan</p>
                        <p class="text-sm font-bold {{ $purchase->remaining_bill > 0 ? 'text-amber-900' : 'text-emerald-800' }} mt-0.5">
                            @if($purchase->payment_status === 'paid')
                                <span class="text-emerald-600">✓ Lunas</span>
                            @elseif($purchase->payment_status === 'partial')
                                <span class="text-amber-600">Sisa: Rp {{ number_format($purchase->remaining_bill, 0, ',', '.') }}</span>
                            @else
                                <span class="text-rose-600">Rp {{ number_format($purchase->remaining_bill, 0, ',', '.') }} (Belum Bayar)</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if($purchase->keterangan)
            <div class="mb-6 flex items-start gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="w-10 h-10 rounded-lg bg-slate-200/50 text-slate-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                </div>
                <div class="min-w-0 pt-0.5">
                    <p class="text-xs font-medium text-slate-500 mb-0.5">Keterangan / Referensi</p>
                    <p class="text-sm font-medium text-slate-700">{{ $purchase->keterangan }}</p>
                </div>
            </div>
            @endif

            @if($purchase->foto_nota)
            <div class="mb-2">
                <p class="text-xs font-semibold text-slate-600 mb-2 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Foto Nota / Bukti Pembelian
                </p>
                <a href="{{ asset('storage/' . $purchase->foto_nota) }}" target="_blank" class="inline-block rounded-xl overflow-hidden border-2 border-slate-200 hover:border-indigo-400 transition-all shadow-sm hover:shadow-md">
                    <img src="{{ asset('storage/' . $purchase->foto_nota) }}" alt="Foto Nota" class="w-48 h-auto object-cover">
                </a>
            </div>
            @endif
        </div>
    </div>

    {{-- Items Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Item Produk
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">{{ $purchase->items->count() }} item</span>
        </div>

        {{-- Mobile: card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @foreach($purchase->items as $item)
            @php $sisa = $item->qty - $item->returned_qty; @endphp
            <div class="p-4 bg-white">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-800 truncate">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                        @if($item->product)
                        <p class="text-xs text-slate-500 font-mono">{{ $item->product->sku }}</p>
                        @endif
                    </div>
                    <p class="text-sm font-bold text-indigo-600 shrink-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 bg-slate-50 p-2 rounded-lg">
                    <span>Qty Beli: <strong class="text-slate-800">{{ $item->qty }}</strong></span>
                    <span>·</span>
                    <span>Diretur: <strong class="{{ $item->returned_qty > 0 ? 'text-rose-600' : 'text-slate-500' }}">{{ $item->returned_qty }}</strong></span>
                    <span>·</span>
                    <span>Sisa: <strong class="text-indigo-600">{{ $sisa }}</strong></span>
                    <span>·</span>
                    <span>× Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                </div>
            </div>
            @endforeach
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600">Total Pembelian</span>
                <span class="text-xl font-bold text-indigo-600">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Desktop: table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">Produk</th>
                        <th class="text-center py-3.5 px-3 font-semibold text-slate-600 w-24">Qty Beli</th>
                        <th class="text-center py-3.5 px-3 font-semibold text-slate-600 w-24">Diretur</th>
                        <th class="text-center py-3.5 px-3 font-semibold text-slate-600 w-24">Sisa</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Harga Beli Satuan</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($purchase->items as $item)
                    @php $sisa = $item->qty - $item->returned_qty; @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                            @if($item->product)
                            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->product->sku }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">{{ $item->qty }}</span>
                        </td>
                        <td class="py-4 px-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg {{ $item->returned_qty > 0 ? 'bg-rose-50 text-rose-700 font-bold border border-rose-100' : 'text-slate-400' }} text-xs">{{ $item->returned_qty }}</span>
                        </td>
                        <td class="py-4 px-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100">{{ $sisa }}</span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-4 px-6 text-right font-bold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Total Pembelian</span>
                <span class="text-2xl font-bold text-indigo-600">Rp {{ number_format($purchase->total, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Riwayat Retur Pembelian Card (jika ada) --}}
    @if($purchase->returns && $purchase->returns->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50/40">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                Riwayat Retur Pembelian ke Supplier
            </h3>
            <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2.5 py-1 rounded-lg">{{ $purchase->returns->count() }} kali retur</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3 px-6 font-semibold text-slate-600">No. Retur</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600">Tanggal</th>
                        <th class="text-right py-3 px-4 font-semibold text-slate-600">Total Nilai</th>
                        <th class="text-left py-3 px-6 font-semibold text-slate-600">Alasan</th>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600">Diproses Oleh</th>
                        <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($purchase->returns as $ret)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-6 font-mono text-xs font-bold text-rose-600">{{ $ret->return_number }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($ret->tanggal)->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-right font-bold text-rose-600">Rp {{ number_format($ret->total_nilai, 0, ',', '.') }}</td>
                        <td class="py-3 px-6 text-slate-600">{{ $ret->alasan }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $ret->processedBy ? $ret->processedBy->name : '-' }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('purchase-returns.show', $ret) }}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="sm:hidden pb-6">
        <a href="{{ route('purchases.index') }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold shadow-sm">
            Kembali ke Daftar
        </a>
    </div>
</div>
</x-app-layout>
