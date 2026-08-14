<x-app-layout>
@section('title', 'Pembelian (Restock)')
<x-slot name="header">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Pembelian (Restock)</h2>
        <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Catat Pembelian Baru
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
        <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Invoice / Referensi</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Supplier</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Dicatat Oleh</th>
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
                    <td class="py-3 px-4 text-slate-600">{{ $purchase->supplier->name }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $purchase->user->name }}</td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-700">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex p-1.5 rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Detail">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan pembelian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $purchases->appends(request()->query())->links() }}</div>
</div>
</x-app-layout>
