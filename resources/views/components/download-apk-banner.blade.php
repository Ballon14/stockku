<div id="apk-banner" class="mx-4 mt-4 sm:mx-6 lg:mx-8">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 sm:p-5 shadow-lg shadow-emerald-600/20 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
            </span>
            <div class="min-w-0">
                <p class="font-semibold text-sm sm:text-base">Unduh Aplikasi Android StockKu</p>
                <p class="text-xs text-emerald-100 mt-0.5">Lebih mudah digunakan di HP kasir — tanpa perlu membuka browser</p>
            </div>
        </div>
        <div class="flex items-center gap-2 ml-auto">
            <a href="{{ route('downloads.apk') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-emerald-700 rounded-xl text-sm font-semibold shadow hover:bg-emerald-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Unduh APK
            </a>
            <button type="button" onclick="document.getElementById('apk-banner').remove(); localStorage.setItem('stockku_apk_banner', '1')" class="p-2 rounded-lg text-emerald-100 hover:bg-white/10 transition-colors" title="Tutup" aria-label="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</div>

<script>
    if (localStorage.getItem('stockku_apk_banner') === '1') {
        document.getElementById('apk-banner').remove();
    }
</script>