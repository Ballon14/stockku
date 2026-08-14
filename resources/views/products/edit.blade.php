<x-app-layout>
@section('title', 'Edit Produk')
<x-slot name="header"><h2 class="text-2xl font-bold text-slate-800">Edit Produk</h2></x-slot>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                    @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" class="w-full rounded-xl border-slate-200 text-sm" required>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                    <input type="text" name="satuan" value="{{ old('satuan', $product->satuan) }}" class="w-full rounded-xl border-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Beli</label>
                    <input type="number" name="harga_beli" value="{{ old('harga_beli', $product->harga_beli) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Jual</label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual', $product->harga_jual) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok', $product->stok) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Minimum Stok</label>
                    <input type="number" name="min_stok" value="{{ old('min_stok', $product->min_stok) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Foto Produk</label>
                    @if($product->foto)
                    <div class="mb-2"><img src="{{ asset('storage/' . $product->foto) }}" class="w-20 h-20 rounded-lg object-cover"></div>
                    @endif
                    <input type="file" name="foto" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-xl border-slate-200 text-sm">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 transition-all">Perbarui</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
