<x-app-layout>
@section('title', 'Detail Pembelian')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Detail Pembelian {{ $purchase->invoice_number }}</h2>
</x-slot>

<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm mb-8">
            <div>
                <span class="text-slate-500 block mb-1">Invoice / Referensi</span>
                <span class="font-mono font-semibold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">{{ $purchase->invoice_number }}</span>
            </div>
            <div>
                <span class="text-slate-500 block mb-1">Tanggal</span>
                <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($purchase->tanggal)->translatedFormat('d F Y') }}</span>
            </div>
            <div>
                <span class="text-slate-500 block mb-1">Supplier</span>
                <span class="font-medium text-slate-800">{{ $purchase->supplier->name }}</span>
            </div>
            <div>
                <span class="text-slate-500 block mb-1">Dicatat Oleh</span>
                <span class="font-medium text-slate-800">{{ $purchase->user->name }}</span>
            </div>
        </div>

        @if($purchase->keterangan)
        <div class="mb-6 bg-slate-50 p-4 rounded-xl text-sm text-slate-700">
            <span class="font-medium block mb-1">Keterangan:</span>
            {{ $purchase->keterangan }}
        </div>
        @endif

        @if($purchase->foto_nota)
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-slate-700 mb-2">Foto Nota / Bukti Pembelian</h3>
            <a href="{{ asset('storage/' . $purchase->foto_nota) }}" target="_blank" class="block w-48 rounded-xl overflow-hidden border border-slate-200 hover:border-indigo-400 transition-colors shadow-sm hover:shadow">
                <img src="{{ asset('storage/' . $purchase->foto_nota) }}" alt="Foto Nota" class="w-full h-auto object-cover">
            </a>
            <p class="text-xs text-slate-400 mt-1">Klik gambar untuk melihat ukuran penuh</p>
        </div>
        @endif

        <h3 class="text-lg font-semibold text-slate-800 mb-3">Item Produk</h3>
        <div class="border rounded-xl border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
    <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-medium text-slate-600">Produk</th>
                        <th class="text-center py-3 px-4 font-medium text-slate-600 w-24">Qty</th>
                        <th class="text-right py-3 px-4 font-medium text-slate-600 w-40">Harga Beli Satuan</th>
                        <th class="text-right py-3 px-4 font-medium text-slate-600 w-40">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedItems = $purchase->items->groupBy('product_id')->map(function ($items) {
                            $first = $items->first();
                            return (object)[
                                'product' => $first->product,
                                'qty' => $items->sum('qty'),
                                'harga' => $first->harga,
                                'subtotal' => $items->sum('subtotal'),
                            ];
                        });
                    @endphp
                    @foreach($groupedItems as $item)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-700 block">{{ $item->product->name }}</span>
                            <span class="text-xs text-slate-400 font-mono">{{ $item->product->sku }}</span>
                        </td>
                        <td class="py-3 px-4 text-center font-medium">{{ $item->qty }}</td>
                        <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="text-right py-4 px-4 font-semibold text-slate-600">Total Pembelian:</td>
                        <td class="text-right py-4 px-4 font-bold text-xl text-slate-800">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
    </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200">← Kembali</a>
        </div>
    </div>
</div>
</x-app-layout>
