<x-app-layout>
@section('title', 'Pembelian (Barang Masuk)')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Pembelian (Barang Masuk)</h2>
            <p class="text-xs text-slate-500">Pencatatan pembelian stok barang masuk dari supplier</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-returns.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-all shadow-sm">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                Retur Pembelian
            </a>
            <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Catat Pembelian Baru
            </a>
        </div>
    </div>
</x-slot>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-medium text-slate-500">Dari</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
        </div>
        <div>
            <label class="text-xs font-medium text-slate-500">Sampai</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
        </div>
        <div class="w-48">
            <label class="text-xs font-medium text-slate-500">Supplier</label>
            <select name="supplier_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                <option value="">Semua Supplier</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" {{ $supplierId == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">Filter</button>
        <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($purchases as $purchase)
        <div class="p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0">
                    <p class="font-mono text-xs font-medium text-indigo-600">{{ $purchase->invoice_number }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($purchase->tanggal)->format('d/m/Y') }} · {{ $purchase->user->name }}</p>
                </div>
                <div class="flex items-center gap-1">
                    @if($purchase->status !== 'returned' && $purchase->items->contains(fn($i) => ($i->qty - $i->returned_qty) > 0))
                    <a href="{{ route('purchase-returns.create', $purchase) }}" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50" title="Retur ke Supplier"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg></a>
                    @endif
                    <a href="{{ route('purchases.show', $purchase) }}" class="shrink-0 p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 truncate">Supplier: <span class="text-slate-700 font-medium">{{ $purchase->supplier->name }}</span></p>
                <div class="text-right ml-3">
                    <p class="font-bold text-slate-800">Rp {{ number_format($purchase->total, 0, ',', '.') }}</p>
                    @if($purchase->status === 'returned')
                    <span class="inline-block mt-0.5 text-[10px] font-semibold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">Diretur Penuh</span>
                    @elseif($purchase->status === 'partial_return')
                    <span class="inline-block mt-0.5 text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded">Retur Sebagian</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-slate-400">Belum ada catatan pembelian.</div>
        @endforelse
    </div>

    <!-- Desktop: table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Invoice / Referensi</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Supplier</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Dicatat Oleh</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Status</th>
                    <th class="text-right py-3 px-4 font-semibold text-slate-600">Total Pembelian</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4">
                        <span class="font-mono text-xs font-medium text-indigo-600 block">{{ $purchase->invoice_number }}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($purchase->tanggal)->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-slate-700 font-medium">{{ $purchase->supplier->name }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $purchase->user->name }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($purchase->status === 'returned')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Diretur Penuh</span>
                        @elseif($purchase->status === 'partial_return')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">Retur Sebagian</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Diterima</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-800">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" title="Lihat Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if($purchase->status !== 'returned' && $purchase->items->contains(fn($i) => ($i->qty - $i->returned_qty) > 0))
                            <a href="{{ route('purchase-returns.create', $purchase) }}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Retur ke Supplier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3"/></svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-400">Belum ada catatan pembelian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $purchases->appends(request()->query())->links() }}</div>
</div>
</x-app-layout>
