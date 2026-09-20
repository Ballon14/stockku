<x-app-layout>
    @section('title', 'Produk')

    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3" x-data>
            <h2 class="text-2xl font-bold text-slate-800">Produk</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.export') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>
                <button type="button" @click="$dispatch('open-import')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-600 rounded-xl text-sm font-medium hover:bg-slate-50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Import CSV
                </button>
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Produk
                </a>
            </div>
        </div>
    </x-slot>

    {{--
    Modal dipindah KE LUAR x-slot header.
    Alasan: elemen position:fixed akan "patah" (ikut parent, bukan viewport) kalau ada
    ancestor dengan transform / filter / backdrop-filter — sering terjadi di komponen header.
    Komunikasi dengan tombol lewat custom event $dispatch('open-import').
--}}
    <div x-data="productImport({
        maxSizeMb: 2,
        action: '{{ route('products.import') }}',
        templateUrl: '{{ route('products.template') }}'
    })" x-on:open-import.window="openModal()" x-on:keydown.escape.window="close()"
        x-show="open" x-cloak style="display:none" class="fixed inset-0 z-50" role="dialog" aria-modal="true"
        aria-labelledby="import-modal-title">
        <!-- Overlay: klik di luar untuk menutup -->
        <div x-show="open" x-transition.opacity.duration.200ms @click="close()"
            class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true"></div>

        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-end sm:items-center justify-center p-0 sm:p-4">
                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.stop
                    class="relative w-full sm:max-w-lg bg-white rounded-t-2xl sm:rounded-2xl shadow-xl overflow-hidden max-h-[92vh] flex flex-col">

                    <form :action="action" method="POST" enctype="multipart/form-data" @submit="onSubmit($event)"
                        class="flex flex-col min-h-0">
                        @csrf

                        <!-- Body (scrollable) -->
                        <div class="p-6 sm:p-8 overflow-y-auto">
                            <!-- Grip bar untuk mobile sheet -->
                            <div class="sm:hidden w-10 h-1 rounded-full bg-slate-200 mx-auto -mt-2 mb-4"></div>

                            <div class="flex items-start justify-between gap-4 mb-6">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex items-center justify-center h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 shrink-0">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-800" id="import-modal-title">Import Data
                                            Produk</h3>
                                        <p class="text-sm text-slate-500 mt-0.5">Unggah file CSV untuk menambahkan
                                            banyak produk sekaligus.</p>
                                    </div>
                                </div>
                                <button type="button" x-ref="closeBtn" @click="close()"
                                    class="text-slate-400 hover:text-slate-600 transition-colors rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 shrink-0"
                                    aria-label="Tutup">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-5">
                                <!-- Dropzone -->
                                <div>
                                    <div class="relative group" @dragover.prevent="isDragging = true"
                                        @dragenter.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                        @drop.prevent="isDragging = false; pick($event.dataTransfer.files)">

                                        <!-- Belum ada file -->
                                        <label x-show="!file"
                                            class="flex flex-col items-center justify-center w-full h-36 px-4 text-center transition border-2 border-dashed rounded-2xl cursor-pointer focus-within:ring-2 focus-within:ring-indigo-500"
                                            :class="isDragging ? 'border-indigo-500 bg-indigo-50' : (error ?
                                                'border-red-300 bg-red-50/40' :
                                                'border-slate-300 bg-white hover:border-indigo-400 hover:bg-indigo-50/30'
                                                )">
                                            <svg class="w-7 h-7 mb-2 transition-colors"
                                                :class="isDragging ? 'text-indigo-500' :
                                                    'text-slate-400 group-hover:text-indigo-500'"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <span class="text-sm font-medium text-slate-600">
                                                <span class="text-indigo-600">Klik untuk pilih file</span> atau tarik
                                                ke sini
                                            </span>
                                            <span class="text-[11px] text-slate-400 mt-1">Format .csv &middot; maksimal
                                                <span x-text="maxSizeMb"></span> MB</span>
                                            <input x-ref="fileInput" type="file" name="file"
                                                accept=".csv,text/csv" class="sr-only"
                                                @change="pick($event.target.files)">
                                        </label>

                                        <!-- Sudah ada file: preview -->
                                        <div x-show="file" x-cloak
                                            class="flex items-center gap-3 w-full p-4 border border-emerald-200 bg-emerald-50/60 rounded-2xl">
                                            <div
                                                class="h-10 w-10 rounded-xl bg-white border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-semibold text-slate-700 truncate"
                                                    x-text="fileName"></p>
                                                <p class="text-xs text-slate-500" x-text="fileSize"></p>
                                            </div>
                                            <button type="button" @click="clearFile()"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0"
                                                aria-label="Hapus file">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Pesan error client-side -->
                                    <p x-show="error" x-cloak x-text="error"
                                        class="mt-2 text-xs font-medium text-red-600 flex items-center gap-1"></p>
                                </div>

                                <!-- Kolom wajib -->
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Kolom
                                        wajib (berurutan)</h4>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach (['Nama Produk', 'Kategori', 'Harga Beli', 'Harga Jual', 'Stok', 'Min Stok', 'Satuan'] as $i => $col)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-1 bg-white text-slate-600 text-[10px] font-semibold rounded-md border border-slate-200 shadow-sm">
                                                <span
                                                    class="text-slate-400">{{ $i + 1 }}</span>{{ $col }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Template -->
                                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Unduh
                                        template</h4>
                                    <p class="text-[11px] text-slate-500 mb-3">Pilih kategori untuk autofill nama
                                        kategori pada template.</p>
                                    <div class="flex gap-2">
                                        <select x-model="category"
                                            class="text-xs rounded-lg border-slate-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500 flex-1 bg-white shadow-sm">
                                            <option value="">Semua (Umum)</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <a
                                            :href="templateUrl +
                                                '?category=' + encodeURIComponent(category)"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg shadow-sm hover:bg-slate-50 hover:text-indigo-600 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Unduh
                                    </a>
                                </div>
                            </div>

                            <!-- Peringatan -->
                            <div class="bg-amber-50/80 rounded-xl p-3 flex gap-3 border border-amber-200">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-xs text-amber-800 leading-relaxed">
                                    <strong class="font-semibold">Perhatian:</strong> SKU dibuat otomatis oleh sistem (misal <span class="font-mono">MKN-0001</span>). Jangan sertakan kolom SKU atau Barcode di dalam file CSV agar proses berhasil.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-slate-50/80 backdrop-blur px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="close()" :disabled="submitting"
                                class="inline-flex justify-center items-center px-4 py-2 bg-white border border-slate-300 text-sm font-semibold rounded-xl text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-all disabled:opacity-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="!file || submitting"
                                class="inline-flex justify-center items-center gap-2 px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl text-sm font-semibold text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md shadow-indigo-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                            <svg x-show="submitting" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span x-text="submitting ? 'Mengimpor
                                                ...' : '
                                            Import Data '"></span>
                                            </button>
                                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function productImport(config) {
                return {
                    open: false,
                    file: null,
                    isDragging: false,
                    error: '',
                    submitting: false,
                    category: '',
                    maxSizeMb: config.maxSizeMb,
                    action: config.action,
                    templateUrl: config.templateUrl,

                    openModal() {
                        this.open = true;
                        document.body.classList.add('overflow-hidden');
                        this.$nextTick(() => this.$refs.closeBtn?.focus());
                    },

                    close() {
                        if (this.submitting) return; // jangan tutup saat request berjalan
                        this.open = false;
                        document.body.classList.remove('overflow-hidden');
                    },

                    pick(files) {
                        this.error = '';
                        if (!files || files.length === 0) return; // drop teks/URL -> tidak crash

                        const f = files[0];

                        if (!/\.csv$/i.test(f.name)) {
                            this.error = 'Format tidak didukung. Gunakan file .csv';
                            this.clearFile();
                            return;
                        }
                        if (f.size > this.maxSizeMb * 1024 * 1024) {
                            this.error = 'Ukuran file melebihi ' + this.maxSizeMb + ' MB.';
                            this.clearFile();
                            return;
                        }
                        if (f.size === 0) {
                            this.error = 'File kosong.';
                            this.clearFile();
                            return;
                        }

                        // Sinkronkan file hasil drag & drop ke <input type="file">
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(f);
                            this.$refs.fileInput.files = dt.files;
                        } catch (e) {
                            /* input sudah terisi lewat dialog pilih file */ }

                        this.file = f;
                    },

                    clearFile() {
                        this.file = null;
                        if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                    },

                    get fileName() {
                        return this.file ? this.file.name : '';
                    },

                    get fileSize() {
                        if (!this.file) return '';
                        const kb = this.file.size / 1024;
                        return kb < 1024 ? kb.toFixed(1) + ' KB' : (kb / 1024).toFixed(2) + ' MB';
                    },

                    onSubmit(e) {
                        if (!this.file) {
                            e.preventDefault();
                            this.error = 'Pilih file CSV terlebih dahulu.';
                            return;
                        }
                        if (this.submitting) {
                            e.preventDefault();
                            return;
                        } // cegah double submit
                        this.submitting = true;
                    },
                }
            }
        </script>
    @endpush

    <!-- Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-slate-500">Cari</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama / SKU / Barcode"
                    class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
            </div>
            <div class="w-48">
                <label class="text-xs font-medium text-slate-500">Kategori</label>
                <select name="category_id"
                    class="w-full mt-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                    <option value="">Semua</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium">Filter</button>
            <a href="{{ route('products.index') }}"
                class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-sm font-medium">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <!-- Mobile: card list -->
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($products as $product)
                <div class="p-4">
                    <div class="flex items-center gap-3 mb-3">
                        @if ($product->foto)
                            <img src="{{ asset('storage/' . $product->foto) }}"
                                class="w-11 h-11 rounded-lg object-cover shrink-0" alt="">
                        @else
                            <div class="w-11 h-11 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-700 truncate">{{ $product->name }}</p>
                            <p class="font-mono text-xs text-slate-400 mt-0.5">{{ $product->sku }} ·
                                {{ $product->category->name }}</p>
                        </div>
                        <span
                            class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            <p>Beli: <span class="font-semibold text-slate-700">Rp
                                    {{ number_format($product->harga_beli, 0, ',', '.') }}</span></p>
                            <p>Jual: <span class="font-semibold text-slate-700">Rp
                                    {{ number_format($product->harga_jual, 0, ',', '.') }}</span></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('products.show', $product) }}"
                                class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"
                                title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg></a>
                            <a href="{{ route('products.edit', $product) }}"
                                class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"><svg
                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg></a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><svg
                                        class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg></button>
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
                                    @if ($product->foto)
                                        <img src="{{ asset('storage/' . $product->foto) }}"
                                            class="w-10 h-10 rounded-lg object-cover" alt="">
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                        </div>
                                    @endif
                                    <span class="font-medium text-slate-700">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-xs">{{ $product->sku }}</td>
                            <td class="py-3 px-4 text-slate-500">{{ $product->category->name }}</td>
                            <td class="py-3 px-4 text-right text-slate-600">Rp
                                {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-700">Rp
                                {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('products.show', $product) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}"
                                        class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                        onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                                        @csrf @method('DELETE')
                                        <button
                                            class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">{{ $products->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
