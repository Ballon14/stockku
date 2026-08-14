<x-app-layout>
@section('title', 'Tambah Produk')
<x-slot name="header"><h2 class="text-2xl font-bold text-slate-800">Tambah Produk</h2></x-slot>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">SKU <span class="text-red-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                    @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    @error('barcode') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        <option value="">Pilih...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="satuan" value="{{ old('satuan', 'pcs') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Beli <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_beli" value="{{ old('harga_beli', 0) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                    @error('harga_beli') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <input type="number" name="harga_jual" value="{{ old('harga_jual', 0) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                    @error('harga_jual') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Stok Awal <span class="text-red-500">*</span></label>
                    <input type="number" name="stok" value="{{ old('stok', 0) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Minimum Stok <span class="text-red-500">*</span></label>
                    <input type="number" name="min_stok" value="{{ old('min_stok', 5) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Foto Produk</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('foto') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('deskripsi') }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
