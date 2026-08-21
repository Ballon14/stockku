<x-app-layout>
@section('title', 'Absensi Harian')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Absensi Harian</h2>
</x-slot>

<div class="max-w-xl mx-auto mt-8">
    @if(!$todayAttendance || !$todayAttendance->clock_in)
    <div class="mb-4 flex items-start gap-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 p-4">
        <span class="shrink-0 w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </span>
        <div>
            <p class="font-semibold text-sm">Anda belum clock-in hari ini</p>
            <p class="text-xs mt-0.5">Clock-in bersifat wajib sebelum Anda dapat menggunakan fitur lain di aplikasi.</p>
        </div>
    </div>
    @elseif($todayAttendance->clock_out)
    <div class="mb-4 flex items-start gap-3 rounded-2xl bg-slate-100 border border-slate-200 text-slate-700 p-4">
        <span class="shrink-0 w-8 h-8 rounded-full bg-slate-400 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </span>
        <div>
            <p class="font-semibold text-sm">Anda sudah clock-out hari ini</p>
            <p class="text-xs mt-0.5">Anda tidak dapat melakukan aktivitas lain sampai clock-in kembali besok.</p>
        </div>
    </div>
    @endif
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center relative overflow-hidden">
        <!-- Decorative bg -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-emerald-50 rounded-full blur-3xl opacity-50"></div>

        <div class="relative z-10">
            <h3 class="text-slate-500 font-medium mb-1">{{ now()->translatedFormat('l, d F Y') }}</h3>
            <div id="live-clock" class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 tracking-tight font-mono mb-8">
                {{ now()->format('H:i:s') }}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-sm font-medium text-slate-500 mb-1">Status Masuk</p>
                    @if($todayAttendance && $todayAttendance->clock_in)
                        <p class="text-xl font-bold text-emerald-600 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->clock_in)->format('H:i') }}</p>
                        <p class="text-xs text-emerald-500 mt-1 flex items-center justify-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Selesai</p>
                    @else
                        <p class="text-xl font-bold text-slate-400 font-mono">--:--</p>
                        <p class="text-xs text-slate-400 mt-1">Belum Clock-In</p>
                    @endif
                </div>
                <div class="bg-slate-50 rounded-2xl p-4">
                    <p class="text-sm font-medium text-slate-500 mb-1">Status Keluar</p>
                    @if($todayAttendance && $todayAttendance->clock_out)
                        <p class="text-xl font-bold text-emerald-600 font-mono">{{ \Carbon\Carbon::parse($todayAttendance->clock_out)->format('H:i') }}</p>
                        <p class="text-xs text-emerald-500 mt-1 flex items-center justify-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Selesai</p>
                    @else
                        <p class="text-xl font-bold text-slate-400 font-mono">--:--</p>
                        <p class="text-xs text-slate-400 mt-1">Belum Clock-Out</p>
                    @endif
                </div>
            </div>

            <div class="space-y-3">
                @if(!$todayAttendance || !$todayAttendance->clock_in)
                <form method="POST" action="{{ route('attendance.clock-in') }}" onsubmit="return confirmForm(this, 'Anda yakin ingin Clock In sekarang?', { title: 'Clock In', confirmText: 'Ya, Clock In' })">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-2xl text-lg font-bold shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        CLOCK IN
                    </button>
                </form>
                @elseif(!$todayAttendance->clock_out)
                <form method="POST" action="{{ route('attendance.clock-out') }}" onsubmit="return confirmForm(this, 'Anda yakin ingin Clock Out sekarang?', { title: 'Clock Out', confirmText: 'Ya, Clock Out' })">
                    @csrf
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-2xl text-lg font-bold shadow-lg shadow-amber-500/30 hover:shadow-amber-500/50 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        CLOCK OUT
                    </button>
                </form>
                @else
                <div class="py-4 bg-slate-100 text-slate-500 rounded-2xl text-lg font-bold border border-slate-200 w-full flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i> Selesai untuk Hari Ini
                    </div>
                @endif
            </div>
            
            <p class="text-xs text-slate-400 mt-6 text-center max-w-sm mx-auto">
                Pastikan izin akses lokasi aktif jika diperlukan. Clock in/out hanya bisa dilakukan sekali per hari.
            </p>
        </div>
    </div>
</div>

<script>
    function updateLiveClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('live-clock').textContent = timeString;
    }
    setInterval(updateLiveClock, 1000);
</script>
</x-app-layout>
