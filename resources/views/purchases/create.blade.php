<x-app-layout>
@section('title', 'Catat Pembelian (Restock)')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Catat Pembelian Baru</h2>
</x-slot>

<div class="max-w-4xl" x-data="purchaseForm()">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('purchases.store') }}" enctype="multipart/form-data">
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
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / No. Referensi Surat Jalan</label>
                    <textarea name="keterangan" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('keterangan') }}</textarea>
                </div>
                <div x-data="{ preview: null }">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Foto Nota / Bukti Pembelian
                        <span class="text-xs text-slate-400 font-normal">(maks 2MB)</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="foto_nota" accept="image/jpeg,image/png,image/webp"
                            class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100"
                            @change="if ($event.target.files[0]) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL($event.target.files[0]); } else { preview = null; }">
                    </div>
                    <template x-if="preview">
                        <div class="mt-2 relative inline-block">
                            <img :src="preview" alt="Preview Nota" class="h-24 rounded-lg border border-slate-200 shadow-sm object-cover">
                            <button type="button" @click="preview = null; $el.closest('[x-data]').querySelector('input[type=file]').value = ''"
                                class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 shadow">×</button>
                        </div>
                    </template>
                    @error('foto_nota') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-slate-800">Item Produk</h3>
                    <button type="button" @click="addItem" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">
                        + Tambah Baris
                    </button>
                </div>

                <!-- Toast notifikasi duplikat -->
                <div x-show="showDuplicateToast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-cloak class="mb-3 flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3">
                    <div class="shrink-0 w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-amber-800 flex-1" x-text="duplicateMessage"></p>
                    <button type="button" @click="showDuplicateToast = false" class="shrink-0 text-amber-400 hover:text-amber-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div class="border rounded-xl border-slate-200 overflow-hidden">
                    <!-- Mobile: card list -->
                    <div class="md:hidden divide-y divide-slate-200">
                        <template x-for="(item, index) in items" :key="'m-' + index">
                            <div class="p-3 space-y-3">
                                <div class="flex items-start gap-2">
                                    <select x-model="items[index].product_id" class="flex-1 min-w-0 rounded-lg border-slate-200 py-1.5 text-sm" required @change="updateHarga(index, $event)">
                                        <option value="">Pilih Produk...</option>
                                        @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" data-harga="{{ $prod->harga_beli }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="shrink-0 p-1.5 rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                <div class="flex items-end gap-2">
                                    <div class="w-20">
                                        <label class="block text-xs text-slate-500 mb-1">Qty</label>
                                        <input type="number" x-model="items[index].qty" min="1" class="w-full text-center rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <label class="block text-xs text-slate-500 mb-1">Harga Beli Satuan</label>
                                        <input type="number" x-model="items[index].harga" min="0" class="w-full text-right rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <label class="block text-xs text-slate-500 mb-1">Subtotal</label>
                                        <span class="font-semibold text-slate-700 text-sm" x-text="'Rp ' + formatRupiah(item.subtotal)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div class="p-3 bg-slate-50 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-600">Total Pembelian:</span>
                            <span class="text-lg font-bold text-slate-800" x-text="'Rp ' + formatRupiah(total)"></span>
                        </div>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden md:block overflow-x-auto">
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
                                        <select x-model="items[index].product_id" class="w-full rounded-lg border-slate-200 py-1.5 text-sm" required @change="updateHarga(index, $event)">
                                            <option value="">Pilih Produk...</option>
                                            @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-harga="{{ $prod->harga_beli }}">{{ $prod->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <input type="number" x-model="items[index].qty" min="1" class="w-20 text-center rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
                                    </td>
                                    <td class="py-2 px-3 text-right">
                                        <input type="number" x-model="items[index].harga" min="0" class="w-full text-right rounded-lg border-slate-200 py-1.5 text-sm" required @input="calculateSubtotal(index)">
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

            <!-- Hidden inputs for actual form submission -->
            <template x-for="(item, index) in items" :key="'submit-' + index">
                <div>
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="items[index].product_id">
                    <input type="hidden" :name="`items[${index}][qty]`" :value="items[index].qty">
                    <input type="hidden" :name="`items[${index}][harga]`" :value="items[index].harga">
                </div>
            </template>

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
        duplicateMessage: '',
        showDuplicateToast: false,
        
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
                // Check if duplicate product — auto-merge qty ke baris yang sudah ada
                const existingIndex = this.items.findIndex((item, i) => i !== index && item.product_id == option.value);
                
                if (existingIndex !== -1) {
                    // Tambahkan qty ke baris yang sudah ada
                    this.items[existingIndex].qty = Number(this.items[existingIndex].qty) + Number(this.items[index].qty);
                    this.calculateSubtotal(existingIndex);

                    // Hapus baris duplikat, atau reset jika hanya 1 baris
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items[index].product_id = '';
                        this.items[index].harga = 0;
                        this.items[index].qty = 1;
                        this.calculateSubtotal(index);
                    }

                    // Tampilkan notifikasi
                    const productName = option.text;
                    this.duplicateMessage = `"${productName}" sudah ada di daftar. Qty otomatis digabungkan ke baris yang ada.`;
                    this.showDuplicateToast = true;
                    setTimeout(() => this.showDuplicateToast = false, 4000);
                    return;
                }

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
