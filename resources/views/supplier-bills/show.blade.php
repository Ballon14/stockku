<x-app-layout>
@section('title', 'Detail Tagihan Supplier')

<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Detail Tagihan Supplier</h2>
            <p class="text-xs text-slate-500 mt-0.5">Rincian kewajiban hutang, kalkulasi retur, dan riwayat pembayaran cicilan</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('purchases.show', $purchase) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-xl text-sm font-semibold hover:bg-indigo-100 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Lihat Faktur Masuk
            </a>
            <a href="{{ route('supplier-bills.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Daftar
            </a>
            @if($purchase->remaining_bill > 0)
            <button type="button" @click="$dispatch('open-pay-modal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 hover:brightness-110 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Catat Pembayaran
            </button>
            @endif
        </div>
    </div>
</x-slot>

<div class="w-full space-y-6" x-data="{ showModal: false }" @open-pay-modal.window="showModal = true">

    {{-- Header Banner Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Banner Gradient --}}
        <div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-purple-800 px-6 sm:px-8 py-5 text-white">
            <div class="flex flex-wrap gap-4 items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0 shadow-inner">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-indigo-200 text-xs font-semibold uppercase tracking-wider">Tagihan Pembelian Supplier</p>
                        <p class="text-white font-bold font-mono text-xl sm:text-2xl mt-0.5 tracking-tight">{{ $purchase->invoice_number }}</p>
                        @if($purchase->supplier_invoice)
                        <p class="text-xs text-indigo-200 font-mono mt-0.5">Faktur Supplier: {{ $purchase->supplier_invoice }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if($purchase->payment_status === 'paid')
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-400/30 backdrop-blur-sm shadow-sm">
                            ✓ Lunas
                        </span>
                    @elseif($purchase->payment_status === 'partial')
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-100 border border-amber-400/30 backdrop-blur-sm shadow-sm">
                            ⟲ Cicilan / Sebagian
                        </span>
                    @else
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-rose-400/20 text-rose-100 border border-rose-400/30 backdrop-blur-sm shadow-sm">
                            Belum Bayar
                        </span>
                    @endif

                    @if($purchase->is_overdue)
                        <span class="px-4 py-1.5 rounded-full text-xs font-bold bg-red-500/30 text-white border border-red-400/40 backdrop-blur-sm shadow-sm">
                            ⚠️ Melewati Jatuh Tempo
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta info grid across full width --}}
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 {{ $purchase->keterangan || $purchase->foto_nota ? 'mb-6' : '' }}">
                {{-- Tanggal Masuk --}}
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Tanggal Barang Masuk</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $purchase->tanggal->format('d F Y') }}</p>
                        <p class="text-xs text-slate-400">Tipe: {{ $purchase->payment_type === 'credit' ? 'Tempo / Kredit' : 'Tunai Langsung' }}</p>
                    </div>
                </div>

                {{-- Tanggal Jatuh Tempo --}}
                <div class="flex items-start gap-3.5 p-4 rounded-xl {{ $purchase->is_overdue ? 'bg-rose-50/60 border border-rose-200' : 'bg-slate-50 border border-slate-100' }}">
                    <div class="w-10 h-10 rounded-lg {{ $purchase->is_overdue ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Batas Jatuh Tempo</p>
                        @if($purchase->due_date)
                        <p class="text-sm font-semibold {{ $purchase->is_overdue ? 'text-rose-600' : 'text-slate-800' }} mt-0.5">
                            {{ $purchase->due_date->format('d F Y') }}
                        </p>
                        <p class="text-xs {{ $purchase->is_overdue ? 'text-rose-500 font-bold' : 'text-slate-400' }}">
                            {{ $purchase->due_date->diffForHumans() }}
                        </p>
                        @else
                        <p class="text-sm font-semibold text-slate-600 mt-0.5">Tidak ditentukan</p>
                        <p class="text-xs text-slate-400">Pembayaran Tunai</p>
                        @endif
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Supplier</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 break-words">{{ $purchase->supplier->name }}</p>
                        @if($purchase->supplier->phone)
                        <p class="text-xs text-slate-400">{{ $purchase->supplier->phone }}</p>
                        @endif
                    </div>
                </div>

                {{-- Petugas Penerima --}}
                <div class="flex items-start gap-3.5 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 font-medium">Dicatat Oleh</p>
                        <p class="text-sm font-semibold text-slate-800 mt-0.5 break-words">{{ $purchase->user->name }}</p>
                        <p class="text-xs text-slate-400">Admin Toko</p>
                    </div>
                </div>
            </div>

            {{-- Keterangan / Foto Nota --}}
            @if($purchase->keterangan || $purchase->foto_nota)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($purchase->keterangan)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block mb-0.5">Catatan Faktur</span>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $purchase->keterangan }}</p>
                    </div>
                </div>
                @endif

                @if($purchase->foto_nota)
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block">Foto Nota Asli</span>
                            <span class="text-xs text-slate-400">Bukti fisik faktur dari supplier</span>
                        </div>
                    </div>
                    <a href="{{ asset('storage/' . $purchase->foto_nota) }}" target="_blank"
                        class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors shadow-sm">
                        Lihat Foto
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Financial Calculation Card (Total Beli - Retur - Terbayar = Sisa Tagihan) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Kalkulasi Nilai Tagihan & Kewajiban
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Pembelian --}}
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-xs text-slate-500 font-medium block">Total Pembelian (Awal)</span>
                <p class="text-xl font-bold text-slate-800 mt-1">Rp {{ number_format($purchase->total, 0, ',', '.') }}</p>
                <span class="text-xs text-slate-400">{{ $purchase->items->count() }} jenis barang masuk</span>
            </div>

            {{-- Potongan Retur --}}
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-xs text-slate-500 font-medium block">Potongan Retur Barang</span>
                <p class="text-xl font-bold {{ $purchase->total_returns_amount > 0 ? 'text-rose-600' : 'text-slate-400' }} mt-1">
                    {{ $purchase->total_returns_amount > 0 ? '-Rp ' . number_format($purchase->total_returns_amount, 0, ',', '.') : 'Rp 0' }}
                </p>
                <span class="text-xs text-slate-400">{{ $purchase->returns->count() }} kali retur pembelian</span>
            </div>

            {{-- Total Pembayaran --}}
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-xs text-slate-500 font-medium block">Total Telah Dibayar</span>
                <p class="text-xl font-bold text-emerald-600 mt-1">
                    Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}
                </p>
                <span class="text-xs text-slate-400">{{ $purchase->payments->count() }} kali pencatatan bayar</span>
            </div>

            {{-- Sisa Tagihan --}}
            <div class="p-4 rounded-2xl {{ $purchase->remaining_bill > 0 ? 'bg-indigo-50/70 border border-indigo-200' : 'bg-emerald-50/70 border border-emerald-200' }}">
                <span class="text-xs {{ $purchase->remaining_bill > 0 ? 'text-indigo-600' : 'text-emerald-700' }} font-bold block uppercase tracking-wider">
                    Sisa Kewajiban Bayar
                </span>
                <p class="text-2xl font-extrabold {{ $purchase->remaining_bill > 0 ? 'text-indigo-700' : 'text-emerald-700' }} mt-1">
                    Rp {{ number_format($purchase->remaining_bill, 0, ',', '.') }}
                </p>
                <span class="text-xs {{ $purchase->remaining_bill > 0 ? 'text-indigo-500 font-medium' : 'text-emerald-600 font-bold' }}">
                    {{ $purchase->remaining_bill > 0 ? 'Harus dilunasi ke supplier' : '✓ Tagihan Telah Lunas Penuh' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Riwayat Pembayaran Tagihan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Pembayaran ke Supplier
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-3 py-1 rounded-lg">
                {{ $purchase->payments->count() }} catatan pembayaran
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600 w-44">No. Pembayaran</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600 w-36">Tgl Bayar</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Nominal</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600 w-36">Metode</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Bank & No. Referensi</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Catatan</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Bukti</th>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600 w-36">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchase->payments as $pay)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6 font-mono text-xs font-bold text-emerald-700">
                            {{ $pay->payment_number }}
                        </td>
                        <td class="py-4 px-4 text-slate-600 text-xs">
                            {{ $pay->tanggal_bayar->format('d/m/Y') }}
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-emerald-600 text-base">
                            Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $pay->metode === 'transfer' ? 'bg-indigo-100 text-indigo-700' : ($pay->metode === 'cash' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ ucfirst($pay->metode) }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @if($pay->bank_tujuan || $pay->nomor_referensi)
                            <p class="font-semibold text-slate-800">{{ $pay->bank_tujuan ?: '-' }}</p>
                            <p class="text-slate-400 font-mono mt-0.5">{{ $pay->nomor_referensi ?: '-' }}</p>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-xs text-slate-600">
                            {{ $pay->catatan ?: '—' }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($pay->bukti_bayar)
                            <a href="{{ asset('storage/' . $pay->bukti_bayar) }}" target="_blank"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Bukti
                            </a>
                            @else
                            <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-600">
                            {{ $pay->createdBy ? $pay->createdBy->name : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 text-sm">
                            Belum ada riwayat pembayaran yang dicatat untuk tagihan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Rincian Item Barang Pembelian --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Rincian Barang Pembelian
            </h3>
            <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-3 py-1 rounded-lg">
                {{ $purchase->items->count() }} produk
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">Produk</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Qty Beli</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Diretur</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600 w-28">Sisa Bersih</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-44">Harga Beli</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600 w-48">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $item)
                    @php $sisaQty = $item->qty - $item->returned_qty; @endphp
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-4 px-6">
                            <p class="font-semibold text-slate-800 leading-snug">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                            @if($item->product)
                            <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-slate-100 text-slate-700 font-semibold text-xs">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($item->returned_qty > 0)
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-rose-50 text-rose-700 font-bold text-xs border border-rose-100">
                                {{ $item->returned_qty }}
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100">
                                {{ $sisaQty }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right font-medium text-slate-600">
                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-slate-800 text-base">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payment Modal --}}
    @if($purchase->remaining_bill > 0)
    <div x-show="showModal" x-cloak style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @keydown.escape.window="showModal = false">
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="showModal = false"
            x-data="{ nominal: {{ $purchase->remaining_bill }} }">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Catat Pembayaran Tagihan</h3>
                    <p class="text-xs text-emerald-100 mt-0.5 font-mono">{{ $purchase->invoice_number }}</p>
                </div>
                <button type="button" @click="showModal = false" class="text-white/80 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form action="{{ route('supplier-bills.payments.store', $purchase) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                {{-- Sisa Info Banner --}}
                <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-medium block">Supplier</span>
                        <strong class="text-sm text-slate-800">{{ $purchase->supplier->name }}</strong>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500 font-medium block">Sisa Kewajiban</span>
                        <strong class="text-base font-bold text-indigo-700">Rp {{ number_format($purchase->remaining_bill, 0, ',', '.') }}</strong>
                    </div>
                </div>

                {{-- Tanggal Bayar & Nominal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Bayar <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_bayar" value="{{ date('Y-m-d') }}" required
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-200">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Nominal (Rp) <span class="text-rose-500">*</span></label>
                            <button type="button" @click="nominal = {{ $purchase->remaining_bill }}" class="text-xs text-emerald-600 font-bold hover:underline">
                                Bayar Lunas
                            </button>
                        </div>
                        <input type="number" name="nominal" x-model.number="nominal" max="{{ $purchase->remaining_bill }}" min="1" step="any" required
                            class="w-full rounded-xl border-slate-200 text-sm font-semibold text-slate-800 focus:border-emerald-500 focus:ring-emerald-200">
                    </div>
                </div>

                {{-- Metode Pembayaran & Bank Tujuan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select name="metode" required class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-200">
                            <option value="transfer">Transfer Bank</option>
                            <option value="cash">Tunai (Cash)</option>
                            <option value="giro">Giro / Cek</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Bank / Akun Tujuan</label>
                        <input type="text" name="bank_tujuan" placeholder="Contoh: BCA, Mandiri"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-200">
                    </div>
                </div>

                {{-- No Referensi & Bukti Bayar --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. Referensi / Transaksi</label>
                        <input type="text" name="nomor_referensi" placeholder="No. referensi transfer"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Upload Bukti Transfer</label>
                        <input type="file" name="bukti_bayar" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan opsional mengenai pembayaran ini..."
                        class="w-full rounded-xl border-slate-200 text-sm focus:border-emerald-500 focus:ring-emerald-200"></textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                        class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-semibold shadow-lg shadow-emerald-500/30 hover:brightness-110 transition-all">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
</x-app-layout>
