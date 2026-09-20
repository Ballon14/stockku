<x-app-layout>
@section('title', 'Produk')
<x-slot name="header">
    <div class="flex items-center justify-between flex-wrap gap-3" x-data="{ showImport: false }">
        <h2 class="text-2xl font-bold text-slate-800">Produk</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.export') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <button @click="showImport = true" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </button>
            <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Produk
            </a>
        </div>

        <!-- Import Modal -->
        <div x-show="showImport" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showImport" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showImport" x-transition.scale.origin.bottom class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white">
                            <div class="p-6 sm:p-8">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-800" id="modal-title">Import Data Produk</h3>
                                            <p class="text-sm text-slate-500 mt-0.5">Unggah file CSV untuk menambahkan banyak produk sekaligus.</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showImport = false" class="text-slate-400 hover:text-slate-500 transition-colors focus:outline-none">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="space-y-6">
                                    <!-- Area Drag and Drop -->
                                    <div x-data="{ fileName: '', isDragging: false }">
                                        <div class="relative group" 
                                             @dragover.prevent="isDragging = true" 
                                             @dragleave.prevent="isDragging = false" 
                                             @drop.prevent="isDragging = false; fileName = $event.dataTransfer.files[0].name; $refs.fileInput.files = $event.dataTransfer.files">
                                            
                                            <label class="flex justify-center w-full h-32 px-4 transition bg-white border-2 border-slate-300 border-dashed rounded-2xl appearance-none cursor-pointer hover:border-indigo-400 focus:outline-none"
                                                   :class="{ 'border-indigo-500 bg-indigo-50': isDragging }">
                                                <span class="flex items-center space-x-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-400 group-hover:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                    </svg>
                                                    <span class="font-medium text-slate-500 group-hover:text-indigo-600 transition-colors">
                                                        <span x-text="fileName ? fileName : 'Klik pilih file CSV atau drag ke sini'"></span>
                                                    </span>
                                                </span>
                                                <input x-ref="fileInput" type="file" name="file" accept=".csv" class="hidden" required @change="fileName = $event.target.files[0].name">
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Informasi Kolom & Template -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Kolom Wajib (Berurutan)</h4>
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Nama Produk</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Kategori</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Harga Beli</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Harga Jual</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Stok</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Min Stok</span>
                                                <span class="px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">Satuan</span>
                                            </div>
                                        </div>

                                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-col justify-between">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unduh Template</h4>
                                                <p class="text-[11px] text-slate-500 mb-2">Pilih kategori untuk autofill nama kategori.</p>
                                            </div>
                                            <div class="flex gap-2">
                                                <select id="template-category" class="text-xs rounded-lg border-slate-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 flex-1 bg-white shadow-sm">
                                                    <option value="">Semua (Umum)</option>
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" onclick="window.location.href='{{ route('products.template') }}?category=' + encodeURIComponent(document.getElementById('template-category').value)" class="px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Unduh
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Warning Alert -->
                                    <div class="bg-amber-50/80 rounded-xl p-3 flex gap-3 border border-amber-200">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-amber-800 leading-relaxed">
                                                <strong class="font-semibold">Perhatian:</strong> SKU akan dibuat otomatis oleh sistem (Misal: MKN-0001). Jangan masukkan kolom SKU atau Barcode di dalam file CSV agar proses berhasil.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50/80 backdrop-blur px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="showImport = false" class="inline-flex justify-center items-center px-4 py-2 bg-white border border-slate-300 text-sm font-semibold rounded-xl text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-all">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex justify-center items-center px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md shadow-indigo-500/30 transition-all">
                                Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-slot>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs font-medium text-slate-500">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nama / SKU / Barcode" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
        </div>
        <div class="w-48">
            <label class="text-xs font-medium text-slate-500">Kategori</label>
            <select name="category_id" class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                <option value="">Semua</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium">Filter</button>
        <a href="{{ route('products.index') }}" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium">Reset</a>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($products as $product)
        <div class="p-4">
            <div class="flex items-center gap-3 mb-3">
                @if($product->foto)
                <img src="{{ asset('storage/' . $product->foto) }}" class="w-11 h-11 rounded-lg object-cover shrink-0" alt="">
                @else
                <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-700 truncate">{{ $product->name }}</p>
                    <p class="font-mono text-xs text-slate-400 mt-0.5">{{ $product->sku }} · {{ $product->category->name }}</p>
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    <p>Beli: <span class="font-semibold text-slate-700">Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</span></p>
                    <p>Jual: <span class="font-semibold text-slate-700">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</span></p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('products.show', $product) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600" title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                    <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-slate-400">Belum ada produk.</div>
        @endforelse
    </div>

    <!-- Desktop: table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">#</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Produk</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">SKU</th>
                    <th class="text-left py-3 px-4 font-semibold text-slate-600">Kategori</th>
                    <th class="text-right py-3 px-4 font-semibold text-slate-600">Harga Beli</th>
                    <th class="text-right py-3 px-4 font-semibold text-slate-600">Harga Jual</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Stok</th>
                    <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $i => $product)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4 text-slate-500">{{ $products->firstItem() + $i }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            @if($product->foto)
                            <img src="{{ asset('storage/' . $product->foto) }}" class="w-10 h-10 rounded-lg object-cover" alt="">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            @endif
                            <span class="font-medium text-slate-700">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-slate-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $product->category->name }}</td>
                    <td class="py-3 px-4 text-right text-slate-600">Rp {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-700">Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('products.show', $product) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-slate-400">Belum ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $products->appends(request()->query())->links() }}</div>
</div>
</x-app-layout>
