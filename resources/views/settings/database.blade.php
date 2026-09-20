<x-app-layout>
    @section('title', 'Pengaturan Database')

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Pengaturan Database</h2>
                <p class="text-sm text-slate-500 mt-1">Kelola pencadangan (backup) dan pemulihan (restore) data sistem.</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Pesan Sukses / Error -->
        <x-flash-notifications />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Kartu Backup -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
                <div class="p-6 sm:p-8 flex-1">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Backup Database</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Unduh salinan seluruh data sistem (produk, penjualan, stok, karyawan) dalam format <strong>.sql</strong>. Lakukan pencadangan ini secara berkala untuk mencegah kehilangan data akibat kerusakan sistem atau hal tak terduga lainnya.
                    </p>
                    
                    <div class="bg-indigo-50/50 rounded-xl p-4 border border-indigo-100 flex gap-3 mb-6">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-xs text-indigo-800 leading-relaxed">
                            Proses ini mungkin membutuhkan waktu beberapa detik tergantung besarnya data Anda. Harap tidak menutup tab saat proses mengunduh berjalan.
                        </div>
                    </div>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 mt-auto">
                    <a href="{{ route('settings.database.backup') }}" class="w-full inline-flex justify-center items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl shadow-md shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:from-indigo-700 hover:to-purple-700 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Backup (.sql)
                    </a>
                </div>
            </div>

            <!-- Kartu Restore -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full" x-data="restoreHandler()">
                <div class="p-6 sm:p-8 flex-1">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 uppercase tracking-wider">
                            Danger Zone
                        </span>
                    </div>
                    
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Restore Database</h3>
                    <p class="text-sm text-slate-500 leading-relaxed mb-6">
                        Pulihkan data sistem menggunakan file <strong>.sql</strong> hasil backup sebelumnya. 
                    </p>

                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 flex gap-3 mb-6">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="text-xs text-amber-800 leading-relaxed">
                            <strong class="font-bold">Peringatan Kritis:</strong> 
                            Melakukan restore akan <strong>MENGHAPUS & MENIMPA</strong> seluruh data yang ada saat ini dengan data dari file backup. Pastikan file yang Anda unggah benar.
                        </div>
                    </div>

                    <form id="restoreForm" action="{{ route('settings.database.restore') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Dropzone -->
                        <div class="relative group" 
                            @dragover.prevent="isDragging = true" 
                            @dragenter.prevent="isDragging = true" 
                            @dragleave.prevent="isDragging = false" 
                            @drop.prevent="isDragging = false; pick($event.dataTransfer.files)">
                            
                            <label x-show="!file" class="flex flex-col items-center justify-center w-full h-32 px-4 text-center transition border-2 border-dashed rounded-2xl cursor-pointer focus-within:ring-2 focus-within:ring-rose-500"
                                :class="isDragging ? 'border-rose-500 bg-rose-50' : (error ? 'border-red-300 bg-red-50/40' : 'border-slate-300 bg-white hover:border-rose-400 hover:bg-rose-50/30')">
                                <svg class="w-6 h-6 mb-2 transition-colors" :class="isDragging ? 'text-rose-500' : 'text-slate-400 group-hover:text-rose-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-sm font-medium text-slate-600">
                                    <span class="text-rose-600">Klik pilih file</span> atau tarik .sql ke sini
                                </span>
                                <input x-ref="fileInput" type="file" name="sql_file" accept=".sql" class="sr-only" @change="pick($event.target.files)">
                            </label>

                            <!-- Preview file -->
                            <div x-show="file" x-cloak class="flex items-center gap-3 w-full p-4 border border-rose-200 bg-rose-50/50 rounded-2xl">
                                <div class="h-10 w-10 rounded-xl bg-white border border-rose-200 flex items-center justify-center text-rose-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-700 truncate" x-text="fileName"></p>
                                    <p class="text-xs text-slate-500" x-text="fileSize"></p>
                                </div>
                                <button type="button" @click="clearFile()" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors shrink-0" aria-label="Hapus file">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <p x-show="error" x-cloak x-text="error" class="mt-2 text-xs font-medium text-red-600 flex items-center gap-1"></p>
                    </form>
                </div>
                <div class="p-6 bg-slate-50 border-t border-slate-100 mt-auto flex gap-3">
                    <button type="button" @click="submitForm()" :disabled="!file || submitting" class="w-full inline-flex justify-center items-center gap-2 px-6 py-3 bg-white border-2 border-rose-600 text-rose-600 font-bold rounded-xl hover:bg-rose-50 transition-all focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="submitting" x-cloak class="animate-spin h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span x-text="submitting ? 'Memulihkan...' : 'Timpa & Restore Database'"></span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script>
        function restoreHandler() {
            return {
                file: null,
                isDragging: false,
                error: '',
                submitting: false,

                pick(files) {
                    this.error = '';
                    if (!files || files.length === 0) return;

                    const f = files[0];
                    if (!f.name.toLowerCase().endsWith('.sql')) {
                        this.error = 'Format file salah. Harus menggunakan file .sql';
                        this.clearFile();
                        return;
                    }
                    if (f.size === 0) {
                        this.error = 'File kosong.';
                        this.clearFile();
                        return;
                    }

                    try {
                        const dt = new DataTransfer();
                        dt.items.add(f);
                        this.$refs.fileInput.files = dt.files;
                    } catch(e) {}

                    this.file = f;
                },

                clearFile() {
                    this.file = null;
                    if(this.$refs.fileInput) this.$refs.fileInput.value = '';
                },

                get fileName() { return this.file ? this.file.name : ''; },
                
                get fileSize() {
                    if (!this.file) return '';
                    const mb = this.file.size / (1024 * 1024);
                    return mb < 1 ? (this.file.size / 1024).toFixed(1) + ' KB' : mb.toFixed(2) + ' MB';
                },

                submitForm() {
                    if(!this.file || this.submitting) return;
                    
                    if(confirm('PENGHAPUSAN DATA: Anda yakin ingin menimpa database ini? Tindakan ini MENGHAPUS semua data yang ada dan tidak bisa dibatalkan.')) {
                        this.submitting = true;
                        document.getElementById('restoreForm').submit();
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
