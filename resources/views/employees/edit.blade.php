<x-app-layout>
@section('title', 'Edit Karyawan')
<x-slot name="header"><h2 class="text-2xl font-bold text-slate-800">Edit Karyawan</h2></x-slot>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}">@csrf @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Nama</label><input type="text" name="nama" value="{{ old('nama', $employee->nama) }}" class="w-full rounded-xl border-slate-200 text-sm" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Jabatan</label><input type="text" name="jabatan" value="{{ old('jabatan', $employee->jabatan) }}" class="w-full rounded-xl border-slate-200 text-sm" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">No. Kontak</label><input type="text" name="no_kontak" value="{{ old('no_kontak', $employee->no_kontak) }}" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Masuk</label><input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $employee->tanggal_masuk->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-200 text-sm" required></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label><textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-sm">{{ old('alamat', $employee->alamat) }}</textarea></div>
                @if($employee->user)
                <hr class="border-slate-200">
                <input type="hidden" name="create_account" value="1">
                <div class="space-y-4 bg-slate-50 p-4 rounded-xl">
                    <h4 class="text-sm font-semibold text-slate-700">Akun Login</h4>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Email Akun</label><input type="email" name="user_email" value="{{ old('user_email', $employee->user->email) }}" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label><input type="password" name="user_password" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <select name="user_role" class="w-full rounded-xl border-slate-200 text-sm">
                            <option value="karyawan" {{ $employee->user->roles->first()?->name == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="kasir" {{ $employee->user->roles->first()?->name == 'kasir' ? 'selected' : '' }}>Kasir</option>
                            <option value="admin" {{ $employee->user->roles->first()?->name == 'admin' ? 'selected' : '' }}>Admin</option>

                        </select>
                    </div>
                </div>
                @endif
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 transition-all">Perbarui</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
