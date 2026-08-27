<x-app-layout>
@section('title', 'Master Shift')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-slate-800">Master Shift</h2>
        <a href="{{ route('shifts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Shift
        </a>
    </div>
</x-slot>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">#</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Nama Shift</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Jam Masuk</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Jam Keluar</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $i => $shift)
            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                <td class="py-3 px-4 text-slate-500">{{ $i + 1 }}</td>
                <td class="py-3 px-4 font-medium text-slate-700">{{ $shift->name }}</td>
                <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $shift->start_time->format('H:i') }}</td>
                <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $shift->end_time->format('H:i') }}</td>
                <td class="py-3 px-4 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <a href="{{ route('shifts.edit', $shift) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('shifts.destroy', $shift) }}" onsubmit="return confirmForm(this, 'Yakin hapus shift ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada shift.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
</x-app-layout>
