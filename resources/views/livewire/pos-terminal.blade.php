<div>
    @if(session()->has('pos-success'))
    <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="text-sm font-medium text-emerald-800">{{ session('pos-success') }}</span>
        </div>
        @if($lastSaleId)
        <a href="{{ route('sales.receipt', $lastSaleId) }}" target="_blank" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900 underline">Cetak Struk →</a>
        @endif
    </div>
    @endif

    @if(session()->has('pos-error'))
    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 p-4">
        <span class="text-sm font-medium text-red-800">{{ session('pos-error') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-12rem)]">
        <!-- Left: Product Search -->
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800 mb-3">Cari Produk</h3>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Nama produk / SKU / barcode..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-sm transition-all" autofocus>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                @forelse($products as $product)
                <button wire:click="addToCart({{ $product->id }})" class="w-full text-left p-3 rounded-xl border border-slate-100 hover:border-indigo-300 hover:bg-indigo-50/50 transition-all group">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-700 group-hover:text-indigo-700">{{ $product->name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $product->sku }} · Stok: {{ $product->stok }} {{ $product->satuan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-indigo-600">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</p>
                            <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-500 ml-auto mt-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                    </div>
                </button>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-slate-400">Produk tidak ditemukan</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Cart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">
                    Keranjang
                    @if(count($cart) > 0)
                    <span class="ml-2 bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ count($cart) }} item</span>
                    @endif
                </h3>
                @if(count($cart) > 0)
                <button wire:click="clearCart" class="text-sm text-red-500 hover:text-red-700 font-medium">Hapus Semua</button>
                @endif
            </div>

            <div class="flex-1 overflow-y-auto">
                @if(count($cart) > 0)
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr>
                            <th class="text-left py-3 px-4 font-medium text-slate-500">Produk</th>
                            <th class="text-center py-3 px-4 font-medium text-slate-500 w-20">Qty</th>
                            <th class="text-right py-3 px-4 font-medium text-slate-500">Harga</th>
                            <th class="text-right py-3 px-4 font-medium text-slate-500">Subtotal</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $key => $item)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50" wire:key="{{ $key }}">
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-700">{{ $item['name'] }}</p>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <input type="number" wire:change="updateQty('{{ $key }}', $event.target.value)" value="{{ $item['qty'] }}" min="1" max="{{ $item['stok'] }}" class="w-16 text-center rounded-lg border border-slate-200 py-1 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                            </td>
                            <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            <td class="py-3 px-2">
                                <button wire:click="removeItem('{{ $key }}')" class="p-1 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <svg class="w-16 h-16 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <p class="text-slate-400 font-medium">Keranjang kosong</p>
                    <p class="text-slate-300 text-sm mt-1">Cari dan tambahkan produk</p>
                </div>
                @endif
            </div>

            <!-- Footer: Total & Checkout -->
            @if(count($cart) > 0)
            <div class="border-t border-slate-200 p-4 bg-slate-50/50">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-4">
                        <div>
                            <label class="text-xs text-slate-500">Diskon Total (Rp)</label>
                            <input type="number" wire:model.live="diskon" min="0" class="w-32 rounded-lg border border-slate-200 py-1.5 px-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200">
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Subtotal: Rp {{ number_format($this->subtotal, 0, ',', '.') }}</p>
                        <p class="text-xl font-bold text-slate-800">Total: Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4 pt-3 border-t border-slate-200">
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Uang dari Pelanggan (Rp)</label>
                        <input type="number" wire:model.live="bayar" min="0" class="w-full rounded-xl border border-slate-300 py-2.5 px-3 text-lg font-bold text-slate-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200" placeholder="0">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-700 block mb-1">Kembalian (Rp)</label>
                        <div class="w-full rounded-xl border border-slate-200 py-2.5 px-3 text-lg font-bold bg-white {{ $this->kembalian > 0 ? 'text-emerald-600' : 'text-slate-800' }}">
                            {{ number_format($this->kembalian, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <input type="text" wire:model="catatan" class="w-full rounded-xl border border-slate-200 py-2 px-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-200" placeholder="Catatan transaksi (opsional)...">
                </div>

                <button wire:click="processPayment" wire:confirm="Apakah Anda yakin uang pelanggan sudah sesuai dan ingin memproses transaksi ini?" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all text-lg flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" {{ $this->bayar < $this->grandTotal ? 'disabled' : '' }}>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Proses Transaksi
                </button>
            </div>
            @endif
        </div>
    </div>
</div>
