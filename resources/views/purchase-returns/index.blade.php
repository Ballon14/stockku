<x-app-layout>
@section('title', 'Retur Pembelian (Supplier)')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-rose-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Retur Pembelian (Supplier)</h2>
                <p class="text-xs text-slate-500">Pengembalian barang masuk kepada supplier</p>
            </div>
        </div>
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Pilih dari Pembelian
        </a>
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
        <a href="{{ route('purchase-returns.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($returns as $return)
        <div class="p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0">
                    <p class="font-mono text-xs font-bold text-rose-600">{{ $return->return_number }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ \Carbon\Carbon::parse($return->tanggal)->format('d/m/Y') }} · {{ $return->processedBy ? $return->processedBy->name : '-' }}</p>
                </div>
                <a href="{{ route('purchase-returns.show', $return) }}" class="shrink-0 p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
            </div>
            <div class="space-y-1 mb-2 text-xs">
                <p><span class="text-slate-500">Invoice Beli: </span><a href="{{ route('purchases.show', $return->purchase_id) }}" class="font-mono font-medium text-indigo-600 hover:underline">{{ $return->purchase->invoice_number }}</a></p>
                <p><span class="text-slate-500">Supplier: </span><span class="font-medium text-slate-700">{{ $return->supplier->name }}</span></p>
                @if($return->alasan)
                <p class="text-slate-500 italic">"{{ Str::limit($return->alasan, 50) }}"</p>
                @endif
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-slate-50 text-xs">
                <span class="text-slate-500">Total Nilai Retur:</span>
                <span class="font-bold text-rose-600 text-sm">Rp {{ number_format($return->total_nilai, 0, ',', '.') }}</span>
            </div>
        </div>
        @empty
        <div class="py-12 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            <p>Belum ada catatan retur pembelian.</p>
        </div>
        @endforelse
    </div>

    <!-- Desktop: table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">No. Retur</th>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Invoice Pembelian</th>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Supplier</th>
                    <th class="text-right py-3.5 px-4 font-semibold text-slate-600">Total Nilai Retur</th>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Alasan</th>
                    <th class="text-left py-3.5 px-4 font-semibold text-slate-600">Diproses Oleh</th>
                    <th class="text-center py-3.5 px-4 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($returns as $return)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="py-3 px-4">
                        <span class="font-mono text-xs font-bold text-rose-600 block">{{ $return->return_number }}</span>
                    </td>
                    <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($return->tanggal)->format('d/m/Y') }}</td>
                    <td class="py-3 px-4">
                        <a href="{{ route('purchases.show', $return->purchase_id) }}" class="font-mono text-xs font-semibold text-indigo-600 hover:underline">{{ $return->purchase->invoice_number }}</a>
                    </td>
                    <td class="py-3 px-4 font-medium text-slate-800">{{ $return->supplier->name }}</td>
                    <td class="py-3 px-4 text-right font-bold text-rose-600">Rp {{ number_format($return->total_nilai, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-slate-500 max-w-xs truncate">{{ Str::limit($return->alasan, 40) }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $return->processedBy ? $return->processedBy->name : '-' }}</td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('purchase-returns.show', $return) }}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" title="Lihat Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        <p>Belum ada catatan retur pembelian.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $returns->links() }}</div>
</div>
</x-app-layout>
