<x-app-layout>
@section('title', 'Tambah Shift')
<x-slot name="header">
    <div class="flex items-center gap-3">
        <a href="{{ route('shifts.index') }}" class="p-2 -ml-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Tambah Shift</h2>
    </div>
</x-slot>

<div class="max-w-2xl bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <form method="POST" action="{{ route('shifts.store') }}" onsubmit="return confirmForm(this, 'Yakin ingin menyimpan shift ini?', { title: 'Konfirmasi Simpan' })">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Nama Shift</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-200">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jam Masuk (Start Time)</label>
                    <input type="text" name="start_time" value="{{ old('start_time') }}" required placeholder="08:00" pattern="^([01]?\d|2[0-3]):[0-5]\d$" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-200">
                    <p class="mt-1 text-xs text-slate-400">Format: HH:MM (24 jam), contoh: 08:00</p>
                    @error('start_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jam Keluar (End Time)</label>
                    <input type="text" name="end_time" value="{{ old('end_time') }}" required placeholder="17:00" pattern="^([01]?\d|2[0-3]):[0-5]\d$" class="mt-1 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-200">
                    <p class="mt-1 text-xs text-slate-400">Format: HH:MM (24 jam), contoh: 17:00</p>
                    @error('end_time') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('shifts.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-colors">Simpan Shift</button>
        </div>
    </form>
</div>
</x-app-layout>
