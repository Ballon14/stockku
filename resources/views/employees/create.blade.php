<x-app-layout>
@section('title', 'Tambah Karyawan')
<x-slot name="header"><h2 class="text-2xl font-bold text-slate-800">Tambah Karyawan</h2></x-slot>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <form method="POST" action="{{ route('employees.store') }}">@csrf
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Nama <span class="text-red-500">*</span></label><input type="text" name="nama" value="{{ old('nama') }}" class="w-full rounded-xl border-slate-200 text-sm" required>@error('nama')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Jabatan <span class="text-red-500">*</span></label><input type="text" name="jabatan" value="{{ old('jabatan') }}" class="w-full rounded-xl border-slate-200 text-sm" required></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">No. Kontak</label><input type="text" name="no_kontak" value="{{ old('no_kontak') }}" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-200 text-sm"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Masuk <span class="text-red-500">*</span></label><input type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" class="w-full rounded-xl border-slate-200 text-sm" required></div>
                </div>
                <div><label class="block text-sm font-medium text-slate-700 mb-1">Alamat</label><textarea name="alamat" rows="2" class="w-full rounded-xl border-slate-200 text-sm">{{ old('alamat') }}</textarea></div>
                <hr class="border-slate-200">
                <div class="mb-2">
                    <h3 class="text-sm font-semibold text-slate-800">Akun Login (Wajib)</h3>
                    <p class="text-xs text-slate-500">Setiap karyawan wajib memiliki akun untuk mengakses sistem dan absensi.</p>
                </div>
                <div id="account-fields" class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Email Akun <span class="text-red-500">*</span></label><input type="email" name="user_email" value="{{ old('user_email') }}" class="w-full rounded-xl border-slate-200 text-sm" required>@error('user_email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-red-500">*</span></label><input type="password" name="user_password" class="w-full rounded-xl border-slate-200 text-sm" required>@error('user_password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="user_role" class="w-full rounded-xl border-slate-200 text-sm" required>
                            <option value="karyawan" {{ old('user_role') == 'karyawan' ? 'selected' : '' }}>Karyawan</option>
                            <option value="kasir" {{ old('user_role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                            <option value="admin" {{ old('user_role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-sm font-medium">Batal</a>
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 transition-all">Simpan</button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
