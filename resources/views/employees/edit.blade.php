<x-app-layout>
@section('title', 'Edit Karyawan')
<x-slot name="header"><h2 class="text-2xl font-bold text-slate-800">Edit Karyawan</h2></x-slot>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        @if($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan pengisian data:</p>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('employees.update', $employee) }}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $employee->nama) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('nama')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                        <input type="text" name="jabatan" value="{{ old('jabatan', $employee->jabatan) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('jabatan')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">No. Kontak</label>
                        <input type="text" name="no_kontak" value="{{ old('no_kontak', $employee->no_kontak) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                        @error('no_kontak')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Karyawan</label>
                        <input type="email" name="email" value="{{ old('email', $employee->email) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Masuk <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', optional($employee->tanggal_masuk)->format('Y-m-d')) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('tanggal_masuk')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200">{{ old('alamat', $employee->alamat) }}</textarea>
                    @error('alamat')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <hr class="border-slate-200">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-slate-800">Akun Login (Wajib)</h3>
                    <p class="text-xs text-slate-500">Akun login untuk mengakses sistem POS dan absensi.</p>
                </div>
                <div class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Akun <span class="text-red-500">*</span></label>
                        <input type="email" name="user_email" value="{{ old('user_email', $employee->user?->email) }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                        @error('user_email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password Baru {{ $employee->user ? '(kosongkan jika tidak diubah)' : '<span class="text-red-500">*</span>' }}</label>
                        <input type="password" name="user_password" placeholder="{{ $employee->user ? 'Minimal 8 karakter jika ingin diubah' : 'Minimal 8 karakter' }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" {{ $employee->user ? '' : 'required' }}>
                        @error('user_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="user_role" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-200" required>
                            <option value="karyawan" {{ old('user_role', $employee->user?->roles->first()?->name ?? 'karyawan') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="kasir" {{ old('user_role', $employee->user?->roles->first()?->name) == 'kasir' ? 'selected' : '' }}>Kasir</option>
                            <option value="admin" {{ old('user_role', $employee->user?->roles->first()?->name) == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('user_role')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium hover:bg-slate-200 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/50 transition-all">Perbarui</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
