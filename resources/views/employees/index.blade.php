<x-app-layout>
@section('title', 'Karyawan')
<x-slot name="header">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-slate-800">Data Karyawan</h2>
        <a href="{{ route('employees.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Tambah Karyawan</a>
    </div>
</x-slot>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @forelse($employees as $i => $emp)
        <div class="p-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div class="min-w-0">
                    <p class="font-medium text-slate-700 truncate">{{ $emp->nama }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $emp->jabatan }} · {{ $emp->no_kontak ?? '-' }}</p>
                </div>
                @if($emp->user)
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ $emp->user->roles->first()->name ?? 'user' }}</span>
                @else
                <span class="shrink-0 text-xs text-slate-400">-</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500">Masuk {{ $emp->tanggal_masuk->format('d/m/Y') }}</p>
                <div class="flex items-center gap-1">
                    <a href="{{ route('employees.edit', $emp) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                    <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirmForm(this, 'Yakin hapus karyawan ini?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                </div>
            </div>
        </div>
        @empty
        <div class="py-8 text-center text-slate-400">Belum ada data karyawan.</div>
        @endforelse
    </div>

    <!-- Desktop: table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100"><tr>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">#</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Nama</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Jabatan</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Kontak</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Tgl Masuk</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Akun</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($employees as $i => $emp)
                <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                    <td class="py-3 px-4 text-slate-500">{{ $employees->firstItem() + $i }}</td>
                    <td class="py-3 px-4 font-medium text-slate-700">{{ $emp->nama }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $emp->jabatan }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $emp->no_kontak ?? '-' }}</td>
                    <td class="py-3 px-4 text-slate-600">{{ $emp->tanggal_masuk->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-center">
                        @if($emp->user)
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ $emp->user->roles->first()->name ?? 'user' }}</span>
                        @else
                        <span class="text-xs text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('employees.edit', $emp) }}" class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('employees.destroy', $emp) }}" onsubmit="return confirmForm(this, 'Yakin hapus karyawan ini?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada data karyawan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 border-t border-slate-100">{{ $employees->links() }}</div>
</div>
</x-app-layout>
