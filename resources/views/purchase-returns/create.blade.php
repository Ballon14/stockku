<x-app-layout>
@section('title', 'Form Retur Pembelian ke Supplier')
<x-slot name="header">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchases.show', $purchase) }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-800 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Form Retur Pembelian</h2>
                <p class="text-xs text-slate-500">Invoice: <span class="font-mono font-semibold text-indigo-600">{{ $purchase->invoice_number }}</span> · Supplier: <span class="font-semibold text-slate-700">{{ $purchase->supplier->name }}</span></p>
            </div>
        </div>
        <a href="{{ route('purchases.show', $purchase) }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:text-slate-900 transition-all shadow-sm">
            Kembali ke Pembelian
        </a>
    </div>
</x-slot>

<div class="max-w-4xl" x-data="purchaseReturnForm()">
    {{-- Warning Card --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex gap-3.5 items-start mb-6 shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        </div>
        <div class="text-sm text-amber-900 leading-relaxed">
            <p class="font-bold text-amber-950 mb-0.5">Ketentuan Retur ke Supplier</p>
            <ul class="list-disc ml-4 space-y-0.5 text-xs text-amber-800">
                <li>Memproses retur ini akan <strong>mengurangi stok produk di gudang/toko</strong> (tipe mutasi: <em>out</em>).</li>
                <li>Qty retur tidak boleh melebihi sisa pembelian maupun sisa stok fisik yang tersedia di toko.</li>
                <li>Status transaksi pembelian akan otomatis diperbarui menjadi <em>"Retur Sebagian"</em> atau <em>"Diretur Penuh"</em>.</li>
            </ul>
        </div>
    </div>

    <form method="POST" action="{{ route('purchase-returns.store', $purchase) }}">
        @csrf

        {{-- Return Meta Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Informasi Retur
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Tanggal Retur <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Supplier Tujuan</label>
                    <input type="text" value="{{ $purchase->supplier->name }}" disabled class="w-full rounded-xl border-slate-200 bg-slate-50 text-slate-600 text-sm font-medium">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1.5">Alasan Retur ke Supplier <span class="text-red-500">*</span></label>
                <textarea name="alasan" rows="3" required placeholder="Contoh: Barang cacat dari pabrik, kemasan rusak, masa kedaluwarsa terlalu dekat..." class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('alasan') }}</textarea>
                @error('alasan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Return Items Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Pilih Produk yang Diretur</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Centang produk dan tentukan jumlah unit yang dikembalikan ke supplier</p>
                </div>
                <span class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-lg">
                    <span x-text="selectedItems.length">0</span> terpilih
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-12 px-4 py-3 text-center">
                                <input type="checkbox" @change="toggleAll" x-model="allSelected" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            </th>
                            <th class="text-left py-3 px-4 font-semibold text-slate-600">Produk</th>
                            <th class="text-right py-3 px-4 font-semibold text-slate-600 w-36">Harga Beli</th>
                            <th class="text-center py-3 px-3 font-semibold text-slate-600 w-24">Qty Beli</th>
                            <th class="text-center py-3 px-3 font-semibold text-slate-600 w-24">Sisa Beli</th>
                            <th class="text-center py-3 px-3 font-semibold text-slate-600 w-24">Stok Toko</th>
                            <th class="text-center py-3 px-4 font-semibold text-slate-600 w-36">Qty Retur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchase->items as $item)
                        @php
                            $sisaBeli = $item->qty - $item->returned_qty;
                            $stokToko = $item->product ? $item->product->stok : 0;
                            $maxRetur = max(0, min($sisaBeli, $stokToko));
                            $canReturn = $maxRetur > 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors {{ ! $canReturn ? 'opacity-60 bg-slate-50/40' : '' }}"
                            x-data="{
                                selected: false,
                                maxQty: {{ $maxRetur }},
                                price: {{ $item->harga }},
                                qty: {{ $canReturn ? $maxRetur : 0 }}
                            }">
                            <td class="px-4 py-3.5 text-center">
                                @if($canReturn)
                                <input type="checkbox"
                                    x-model="selected"
                                    @change="updateSelection({{ $item->product_id }}, selected, qty, price)"
                                    class="item-checkbox rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                @else
                                <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <p class="font-semibold text-slate-800">{{ $item->product ? $item->product->name : 'Produk Dihapus' }}</p>
                                @if($item->product)
                                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $item->product->sku }}</p>
                                @endif
                                @if(! $canReturn)
                                    <span class="inline-block mt-1 text-[11px] font-semibold text-red-500 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                                        {{ $sisaBeli <= 0 ? 'Sudah habis diretur' : 'Stok toko 0 (tidak dapat diretur)' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-slate-600">
                                Rp {{ number_format($item->harga, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-3 text-center text-slate-600">
                                {{ $item->qty }}
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <span class="font-semibold {{ $sisaBeli > 0 ? 'text-indigo-600' : 'text-slate-400' }}">{{ $sisaBeli }}</span>
                            </td>
                            <td class="py-3.5 px-3 text-center">
                                <span class="font-semibold {{ $stokToko > 0 ? 'text-emerald-600' : 'text-red-500' }}">{{ $stokToko }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($canReturn)
                                <div class="flex items-center gap-1.5">
                                    <input type="number"
                                        x-model.number="qty"
                                        x-bind:disabled="!selected"
                                        min="1"
                                        x-bind:max="maxQty"
                                        @input="updateQty({{ $item->product_id }}, qty, price)"
                                        class="w-full text-center rounded-xl border-slate-200 py-1.5 text-sm font-semibold focus:border-rose-500 focus:ring-rose-200 disabled:bg-slate-100 disabled:text-slate-400">
                                </div>
                                <p class="text-[10px] text-slate-400 text-center mt-1">Maks: <span x-text="maxQty"></span></p>
                                @else
                                <span class="text-xs text-slate-400 block text-center italic">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary bar --}}
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Estimasi Total Nilai Retur</p>
                    <p class="text-2xl font-bold text-rose-600 mt-0.5">
                        Rp <span x-text="new Intl.NumberFormat('id-ID').format(totalNilai)">0</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('purchases.show', $purchase) }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-100 transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        x-bind:disabled="selectedItems.length === 0"
                        class="px-6 py-2.5 bg-gradient-to-r from-rose-600 to-amber-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        Simpan & Proses Retur ke Supplier
                    </button>
                </div>
            </div>

            {{-- Dynamic Hidden inputs for form submit --}}
            <template x-for="(item, index) in selectedItems" :key="index">
                <div>
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                    <input type="hidden" :name="`items[${index}][qty]`" :value="item.qty">
                </div>
            </template>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseReturnForm', () => ({
        selectedItems: [],
        allSelected: false,

        get totalNilai() {
            return this.selectedItems.reduce((acc, item) => acc + (item.qty * item.price), 0);
        },

        toggleAll(event) {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(cb => {
                if (cb.checked !== this.allSelected) {
                    cb.click();
                }
            });
        },

        updateSelection(productId, isSelected, qty, price) {
            if (isSelected) {
                const exists = this.selectedItems.find(i => i.product_id === productId);
                if (!exists) {
                    this.selectedItems.push({ product_id: productId, qty: parseInt(qty) || 1, price: price });
                }
            } else {
                this.selectedItems = this.selectedItems.filter(i => i.product_id !== productId);
            }
            this.checkAllSelected();
        },

        updateQty(productId, qty, price) {
            const item = this.selectedItems.find(i => i.product_id === productId);
            if (item) {
                item.qty = Math.max(1, parseInt(qty) || 1);
            }
        },

        checkAllSelected() {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            const total = checkboxes.length;
            const checked = document.querySelectorAll('.item-checkbox:checked').length;
            this.allSelected = total > 0 && total === checked;
        }
    }));
});
</script>
</x-app-layout>
