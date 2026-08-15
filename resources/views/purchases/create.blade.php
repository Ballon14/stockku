<x-app-layout>
@section('title', 'Catat Pembelian (Restock)')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Catat Pembelian Baru</h2>
</x-slot>

<div class="max-w-4xl" x-data="purchaseForm()">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('purchases.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Supplier <span class="text-red-500">*</span></label>
                    <select name="supplier_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        <option value="">Pilih Supplier...</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pembelian <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                    @error('tanggal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / No. Referensi Surat Jalan</label>
                    <textarea name="keterangan" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-slate-800">Item Produk</h3>
                    <button type="button" @click="addItem" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">
                        + Tambah Baris
                    </button>
                </div>

                <div class="border rounded-xl border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left py-2 px-3 font-medium text-slate-600">Produk</th>
                                <th class="text-center py-2 px-3 font-medium text-slate-600 w-24">Qty</th>
                                <th class="text-right py-2 px-3 font-medium text-slate-600 w-40">Harga Beli Satuan</th>
                                <th class="text-right py-2 px-3 font-medium text-slate-600 w-40">Subtotal</th>
                                <th class="w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-2 px-3">
                                        <select x-model="item.product_id" :name="`items[${index}][product_id]`" class="w-full rounded-lg border-slate-200 py-1.5 text-sm" required @change="updateHarga(index, $event)">
                                            <option value="">Pilih Produk...</option>
                                            @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-harga="{{ $prod->harga_beli }}">{{ $prod->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <input type="number" x-model="item.qty" :name="`items[${index}][qty]`" min="1" class="w-20 text-center rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" x-model="item.harga" :name="`items[${index}][harga]`" min="0" class="w-full text-right rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <span class="font-semibold text-slate-700" x-text="'Rp ' + formatRupiah(item.subtotal)"></span>
                                    </td>
                                    <td class="py-2 px-2">
                                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="text-right py-3 px-3 font-semibold text-slate-600">Total Pembelian:</td>
                                <td class="text-right py-3 px-3 font-bold text-lg text-slate-800" x-text="'Rp ' + formatRupiah(total)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
    </div>
                </div>
                @error('items') <p class="text-xs text-red-500 mt-2">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 mt-6">
                <a href="{{ route('purchases.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">Simpan & Masukkan Stok</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('purchaseForm', () => ({
        items: [
            { product_id: '', qty: 1, harga: 0, subtotal: 0 }
        ],
        
        get total() {
            return this.items.reduce((sum, item) => sum + Number(item.subtotal), 0);
        },

        addItem() {
            this.items.push({ product_id: '', qty: 1, harga: 0, subtotal: 0 });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        updateHarga(index, event) {
            const select = event.target;
            const option = select.options[select.selectedIndex];
            if (option.value) {
                this.items[index].harga = option.dataset.harga;
                this.calculateSubtotal(index);
            }
        },

        calculateSubtotal(index) {
            const item = this.items[index];
            item.subtotal = Number(item.qty) * Number(item.harga);
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    }));
});
</script>
</x-app-layout>
