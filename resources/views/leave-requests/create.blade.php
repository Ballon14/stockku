<x-app-layout>
@section('title', 'Buat Pengajuan Baru')
<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Buat Pengajuan Izin/Sakit/Cuti</h2>
</x-slot>

<div class="max-w-xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('leave-requests.store') }}">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Pengajuan <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="izin" {{ old('jenis') == 'izin' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500" required>
                            <span class="text-sm font-medium text-slate-700">Izin</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="sakit" {{ old('jenis') == 'sakit' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Sakit</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="cuti" {{ old('jenis') == 'cuti' ? 'checked' : '' }} class="text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-slate-700">Cuti</span>
                        </label>
                    </div>
                    @error('jenis') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('tanggal_mulai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('tanggal_selesai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan / Alasan</label>
                    <textarea name="keterangan" rows="4" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" placeholder="Tuliskan keterangan detail di sini...">{{ old('keterangan') }}</textarea>
                    @error('keterangan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex gap-3 mt-8">
                <a href="{{ route('leave-requests.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
