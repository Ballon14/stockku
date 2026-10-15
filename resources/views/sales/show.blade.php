<x-app-layout>
@section('title', 'Detail Transaksi')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-800">Detail Transaksi {{ $sale->invoice_number }}</h2>
        <div class="flex gap-2">
            <a href="{{ route('sales.index') }}" class="px-3.5 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">← Kembali</a>
            <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/30 transition-all"><i class="fa-solid fa-print"></i> Cetak Struk</a>
        </div>
    </div>
</x-slot>

<div class="max-w-3xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-sm mb-6 pb-6 border-b border-slate-100">
            <div>
                <span class="text-xs text-slate-500 block mb-0.5">Invoice</span>
                <span class="font-mono font-semibold text-indigo-600">{{ $sale->invoice_number }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 block mb-0.5">Tanggal</span>
                <span class="font-medium text-slate-700">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 block mb-0.5">Kasir</span>
                <span class="font-medium text-slate-700">{{ $sale->user->name }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 block mb-0.5">Metode</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sale->payment_method === 'qris' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600' }}">{{ $sale->payment_method === 'qris' ? 'QRIS' : 'Tunai' }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-500 block mb-0.5">Status</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sale->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $sale->status === 'completed' ? 'Selesai' : 'Diretur' }}</span>
            </div>
        </div>

        @if($sale->catatan)
        <div class="mb-6 p-4 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-3">
            <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-900 block mb-1">Catatan Transaksi</span>
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $sale->catatan }}</p>
            </div>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-sm mb-6">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left py-2.5 px-3 font-semibold text-slate-600">Produk</th>
                        <th class="text-center py-2.5 px-3 font-semibold text-slate-600">Qty</th>
                        <th class="text-right py-2.5 px-3 font-semibold text-slate-600">Harga</th>
                        <th class="text-right py-2.5 px-3 font-semibold text-slate-600">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                        <td class="py-2.5 px-3">
                            <span class="font-medium text-slate-700 block">{{ $item->product->name }}</span>
                            @if($item->diskon > 0)
                            <span class="text-xs text-red-500">Diskon: -Rp {{ number_format($item->diskon, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center text-slate-600">{{ $item->qty }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-semibold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-2 pt-4 border-t border-slate-100 text-sm">
            <div class="flex justify-between sm:justify-end sm:gap-8"><span class="text-slate-500">Subtotal</span><span class="font-medium sm:w-36 text-right">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
            @if($sale->diskon > 0)
            <div class="flex justify-between sm:justify-end sm:gap-8"><span class="text-slate-500">Diskon</span><span class="font-medium sm:w-36 text-right text-red-600">- Rp {{ number_format($sale->diskon, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between sm:justify-end sm:gap-8 text-base font-bold pt-2 border-t border-dashed border-slate-200"><span>Grand Total</span><span class="sm:w-36 text-right text-indigo-700">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span></div>
            <div class="flex justify-between sm:justify-end sm:gap-8"><span class="text-slate-500">Bayar</span><span class="font-medium sm:w-36 text-right">Rp {{ number_format($sale->bayar, 0, ',', '.') }}</span></div>
            <div class="flex justify-between sm:justify-end sm:gap-8"><span class="text-slate-500">Kembalian</span><span class="font-semibold sm:w-36 text-right text-emerald-600">Rp {{ number_format($sale->kembalian, 0, ',', '.') }}</span></div>
        </div>
    </div>
</div>
</x-app-layout>
