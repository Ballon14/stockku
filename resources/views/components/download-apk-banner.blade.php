<div id="apk-banner" class="mx-4 mt-4 sm:mx-6 lg:mx-8">
    <div class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-4 sm:p-5 shadow-lg shadow-emerald-600/20 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.6 9.48l1.84-3.18c.16-.31.04-.69-.26-.85-.29-.15-.65-.06-.83.22l-1.88 3.24c-2.86-1.21-6.08-1.21-8.94 0L5.65 5.67c-.19-.29-.58-.38-.87-.2-.28.18-.37.54-.22.83L6.4 9.48C3.3 11.25 1.28 14.44 1 18h22c-.28-3.56-2.3-6.75-5.4-8.52zM7 15.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25zm10 0c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg>
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