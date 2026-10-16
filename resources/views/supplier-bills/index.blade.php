<x-app-layout>
@section('title', 'Tagihan Supplier')

<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tagihan Supplier</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan hutang dagang, jadwal jatuh tempo, dan riwayat pembayaran supplier</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 hover:brightness-110 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pembelian Baru
            </a>
        </div>
    </div>
</x-slot>

<div class="w-full space-y-6" x-data="supplierBillManager()">

    {{-- 4 Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Total Hutang Aktif --}}
        <div class="p-5 rounded-2xl bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Sisa Hutang</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800">Rp {{ number_format($metrics['total_hutang'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Kewajiban aktif ke semua supplier</p>
        </div>

        {{-- Card 2: Tagihan Jatuh Tempo (Overdue) --}}
        <div class="p-5 rounded-2xl bg-white border border-slate-100 shadow-sm relative overflow-hidden {{ $metrics['overdue_count'] > 0 ? 'ring-2 ring-rose-500/20' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Jatuh Tempo (Overdue)</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-rose-600">Rp {{ number_format($metrics['overdue_amount'], 0, ',', '.') }}</p>
            <p class="text-xs text-rose-500 font-medium mt-1">{{ $metrics['overdue_count'] }} faktur melewati batas tempo</p>
        </div>

        {{-- Card 3: Jatuh Tempo Segera (< 7 hari) --}}
        <div class="p-5 rounded-2xl bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Tempo Dalam 7 Hari</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800">Rp {{ number_format($metrics['upcoming_amount'], 0, ',', '.') }}</p>
            <p class="text-xs text-amber-600 font-medium mt-1">{{ $metrics['upcoming_count'] }} faktur jatuh tempo segera</p>
        </div>

        {{-- Card 4: Terbayar Bulan Ini --}}
        <div class="p-5 rounded-2xl bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Terbayar Bulan Ini</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($metrics['lunas_bulan_ini'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-1">Total pembayaran periode {{ now()->translatedFormat('F Y') }}</p>
        </div>
    </div>

    {{-- Filter & Tab Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-100 text-sm">
            <a href="{{ route('supplier-bills.index', array_merge(request()->except('status', 'page'), ['status' => ''])) }}"
                class="px-4 py-2 rounded-xl font-semibold transition-all shrink-0 {{ empty($status) ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                Semua Tagihan
            </a>
            <a href="{{ route('supplier-bills.index', array_merge(request()->except('status', 'page'), ['status' => 'unpaid'])) }}"
                class="px-4 py-2 rounded-xl font-semibold transition-all shrink-0 {{ $status === 'unpaid' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                Belum Bayar
            </a>
            <a href="{{ route('supplier-bills.index', array_merge(request()->except('status', 'page'), ['status' => 'partial'])) }}"
                class="px-4 py-2 rounded-xl font-semibold transition-all shrink-0 {{ $status === 'partial' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                Cicilan / Sebagian
            </a>
            <a href="{{ route('supplier-bills.index', array_merge(request()->except('status', 'page'), ['status' => 'overdue'])) }}"
                class="px-4 py-2 rounded-xl font-semibold transition-all shrink-0 {{ $status === 'overdue' ? 'bg-red-600 text-white shadow-sm' : 'text-rose-600 hover:bg-rose-50' }}">
                ⚠️ Jatuh Tempo
            </a>
            <a href="{{ route('supplier-bills.index', array_merge(request()->except('status', 'page'), ['status' => 'paid'])) }}"
                class="px-4 py-2 rounded-xl font-semibold transition-all shrink-0 {{ $status === 'paid' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50' }}">
                Lunas
            </a>
        </div>

        {{-- Filter Inputs --}}
        <form method="GET" action="{{ route('supplier-bills.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
            @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
            @endif

            <div class="md:col-span-2">
                <label class="text-xs font-medium text-slate-500">Cari Invoice / Faktur Supplier / Supplier</label>
                <div class="relative mt-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nomor invoice atau nama supplier..."
                        class="w-full rounded-xl border-slate-200 text-sm pl-9 focus:border-indigo-500 focus:ring-indigo-200">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-slate-500">Supplier</label>
                <select name="supplier_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ $supplierId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-slate-500">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
            </div>

            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="text-xs font-medium text-slate-500">Tanggal Sampai</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition-all shrink-0">
                    Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Bills Table Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        {{-- Mobile: card list --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($bills as $bill)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-xs font-bold text-indigo-600">{{ $bill->invoice_number }}</p>
                        @if($bill->supplier_invoice)
                        <p class="text-xs text-slate-500 font-mono">Faktur: {{ $bill->supplier_invoice }}</p>
                        @endif
                        <p class="text-sm font-semibold text-slate-800 mt-1">{{ $bill->supplier->name }}</p>
                    </div>
                    <div>
                        @if($bill->payment_status === 'paid')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">✓ Lunas</span>
                        @elseif($bill->payment_status === 'partial')
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">⟲ Cicilan</span>
                        @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Belum Bayar</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-3 rounded-xl">
                    <div>
                        <span class="text-slate-400 block">Jatuh Tempo:</span>
                        @if($bill->due_date)
                        <span class="font-medium {{ $bill->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                            {{ $bill->due_date->format('d/m/Y') }}
                            @if($bill->is_overdue)
                            <span class="text-[10px] bg-rose-100 px-1.5 py-0.5 rounded text-rose-700">Lewat!</span>
                            @endif
                        </span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block">Sisa Tagihan:</span>
                        <span class="text-sm font-bold {{ $bill->remaining_bill > 0 ? 'text-indigo-600' : 'text-emerald-600' }}">
                            Rp {{ number_format($bill->remaining_bill, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <span class="text-xs text-slate-400">Total: Rp {{ number_format($bill->total, 0, ',', '.') }}</span>
                    <div class="flex items-center gap-2">
                        @if($bill->remaining_bill > 0)
                        <button type="button" @click="openPaymentModal({{ $bill->id }}, '{{ $bill->invoice_number }}', '{{ addslashes($bill->supplier->name) }}', {{ $bill->remaining_bill }})"
                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">
                            Bayar
                        </button>
                        @endif
                        <a href="{{ route('supplier-bills.show', $bill) }}"
                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                Tidak ada tagihan supplier yang sesuai filter.
            </div>
            @endforelse
        </div>

        {{-- Desktop: table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-3.5 px-6 font-semibold text-slate-600">No. Invoice & Faktur</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Supplier</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Tgl Masuk</th>
                        <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Jatuh Tempo</th>
                        <th class="text-right py-3.5 px-4 font-semibold text-slate-600">Total Beli</th>
                        <th class="text-right py-3.5 px-4 font-semibold text-slate-600">Potongan Retur</th>
                        <th class="text-right py-3.5 px-4 font-semibold text-slate-600">Sudah Dibayar</th>
                        <th class="text-right py-3.5 px-6 font-semibold text-slate-600">Sisa Tagihan</th>
                        <th class="text-center py-3.5 px-4 font-semibold text-slate-600">Status</th>
                        <th class="text-center py-3.5 px-6 font-semibold text-slate-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $bill->is_overdue ? 'bg-rose-50/20' : '' }}">
                        <td class="py-4 px-6">
                            <a href="{{ route('supplier-bills.show', $bill) }}" class="font-mono text-xs font-bold text-indigo-600 hover:underline block">
                                {{ $bill->invoice_number }}
                            </a>
                            @if($bill->supplier_invoice)
                            <span class="text-xs text-slate-400 font-mono mt-0.5 block">Faktur: {{ $bill->supplier_invoice }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 font-semibold text-slate-800">
                            {{ $bill->supplier->name }}
                        </td>
                        <td class="py-4 px-4 text-slate-600 text-xs">
                            {{ $bill->tanggal->format('d/m/Y') }}
                        </td>
                        <td class="py-4 px-4">
                            @if($bill->due_date)
                            <div class="text-xs">
                                <span class="font-semibold {{ $bill->is_overdue ? 'text-rose-600' : 'text-slate-700' }}">
                                    {{ $bill->due_date->format('d/m/Y') }}
                                </span>
                                @if($bill->is_overdue)
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 ml-1">
                                    Overdue!
                                </span>
                                @endif
                            </div>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right font-medium text-slate-700">
                            Rp {{ number_format($bill->total, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-right">
                            @if($bill->total_returns_amount > 0)
                            <span class="text-rose-600 font-medium">-Rp {{ number_format($bill->total_returns_amount, 0, ',', '.') }}</span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-right font-medium text-emerald-600">
                            Rp {{ number_format($bill->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-6 text-right font-bold text-base {{ $bill->remaining_bill > 0 ? 'text-indigo-700' : 'text-slate-400' }}">
                            Rp {{ number_format($bill->remaining_bill, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($bill->payment_status === 'paid')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">✓ Lunas</span>
                            @elseif($bill->payment_status === 'partial')
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">⟲ Cicilan</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-700">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                @if($bill->remaining_bill > 0)
                                <button type="button" @click="openPaymentModal({{ $bill->id }}, '{{ $bill->invoice_number }}', '{{ addslashes($bill->supplier->name) }}', {{ $bill->remaining_bill }})"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all hover:scale-105">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Bayar
                                </button>
                                @endif
                                <a href="{{ route('supplier-bills.show', $bill) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="font-medium text-slate-500">Tidak ada data tagihan supplier yang ditemukan.</p>
                            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter status atau kata kunci pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($bills->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $bills->links() }}
        </div>
        @endif
    </div>

    {{-- Quick Payment Modal --}}
    <div x-show="showModal" x-cloak style="display: none;"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @keydown.escape.window="closeModal()">
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden" @click.away="closeModal()">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Catat Pembayaran Tagihan</h3>
                    <p class="text-xs text-emerald-100 mt-0.5 font-mono" x-text="activeInvoice"></p>
                </div>
                <button type="button" @click="closeModal()" class="text-white/80 hover:text-white p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body Form --}}
            <form :action="'/supplier-bills/' + activeId + '/payments'" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                {{-- Sisa Info Banner --}}
                <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-500 font-medium block">Supplier</span>
                        <strong class="text-sm text-slate-800" x-text="activeSupplier"></strong>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500 font-medium block">Sisa Tagihan</span>
                        <strong class="text-base font-bold text-indigo-700" x-text="'Rp ' + formatNumber(activeRemaining)"></strong>
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
                            <button type="button" @click="nominal = activeRemaining" class="text-xs text-emerald-600 font-bold hover:underline">
                                Bayar Lunas
                            </button>
                        </div>
                        <input type="number" name="nominal" x-model.number="nominal" :max="activeRemaining" min="1" step="any" required
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
                    <button type="button" @click="closeModal()"
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

</div>

@push('scripts')
<script>
    function supplierBillManager() {
        return {
            showModal: false,
            activeId: null,
            activeInvoice: '',
            activeSupplier: '',
            activeRemaining: 0,
            nominal: 0,

            openPaymentModal(id, invoice, supplier, remaining) {
                this.activeId = id;
                this.activeInvoice = invoice;
                this.activeSupplier = supplier;
                this.activeRemaining = remaining;
                this.nominal = remaining;
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
            },

            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        };
    }
</script>
@endpush
</x-app-layout>
