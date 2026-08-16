<x-app-layout>
@section('title', 'Dashboard')

<x-slot name="header">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
</x-slot>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($data['sales_today'], 0, ',', '.') }}</p>
                <p class="text-xs text-emerald-600 mt-1">{{ $data['sales_count_today'] }} transaksi</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Penjualan Bulan Ini</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">Rp {{ number_format($data['sales_this_month'], 0, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Kehadiran Hari Ini</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $data['attendance_summary']['hadir'] }}/{{ $data['attendance_summary']['total'] }}</p>
                <p class="text-xs text-amber-600 mt-1">{{ $data['attendance_summary']['tidak_hadir'] }} belum hadir</p>
            </div>
            <div class="w-12 h-12 bg-violet-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-slate-500">Stok Menipis</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $data['low_stock']->count() }}</p>
                <p class="text-xs text-red-600 mt-1">produk perlu restock</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Sales Chart -->
    <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Penjualan 7 Hari Terakhir</h3>
        <div class="space-y-3">
            @php
                $maxSale = $data['daily_sales']->max('total') ?: 1;
            @endphp
            @forelse($data['daily_sales'] as $day)
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-500 w-20 shrink-0">{{ \Carbon\Carbon::parse($day->date)->translatedFormat('d M') }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-6 relative overflow-hidden">
                    <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                         style="width: {{ ($day->total / $maxSale) * 100 }}%">
                    </div>
                    <span class="absolute inset-0 flex items-center justify-center text-xs font-medium {{ ($day->total / $maxSale) > 0.5 ? 'text-white' : 'text-slate-600' }}">
                        Rp {{ number_format($day->total, 0, ',', '.') }}
                    </span>
                </div>
                <span class="text-xs text-slate-400 w-12 text-right">{{ $day->count }}x</span>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-8">Belum ada data penjualan</p>
            @endforelse
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Produk Terlaris</h3>
        <div class="space-y-4">
            @forelse($data['top_products'] as $i => $item)
            <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-lg bg-gradient-to-br {{ $i === 0 ? 'from-amber-400 to-orange-500' : ($i === 1 ? 'from-slate-300 to-slate-400' : 'from-amber-600 to-amber-700') }} flex items-center justify-center text-white text-xs font-bold">{{ $i + 1 }}</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $item->product->name }}</p>
                    <p class="text-xs text-slate-400">{{ $item->total_qty }} terjual</p>
                </div>
                <p class="text-sm font-semibold text-slate-700">Rp {{ number_format($item->total_sales, 0, ',', '.') }}</p>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Belum ada data</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
@if($data['low_stock']->count() > 0)
<div class="mt-6 bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        Stok Menipis
    </h3>
        <!-- Mobile: card list -->
    <div class="md:hidden divide-y divide-slate-100">
        @foreach($data['low_stock'] as $prod)
        <div class="py-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-slate-700 truncate">{{ $prod->name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $prod->category->name }}</p>
                </div>
                <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold {{ $prod->stok <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $prod->stok }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">Min. stok: {{ $prod->min_stok }}</p>
        </div>
        @endforeach
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left py-2 px-3 font-medium text-slate-500">Produk</th>
                    <th class="text-left py-2 px-3 font-medium text-slate-500">Kategori</th>
                    <th class="text-center py-2 px-3 font-medium text-slate-500">Stok</th>
                    <th class="text-center py-2 px-3 font-medium text-slate-500">Min. Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['low_stock'] as $prod)
                <tr class="border-b border-slate-50 hover:bg-slate-50">
                    <td class="py-2 px-3 font-medium text-slate-700">{{ $prod->name }}</td>
                    <td class="py-2 px-3 text-slate-500">{{ $prod->category->name }}</td>
                    <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $prod->stok <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $prod->stok }}</span></td>
                    <td class="py-2 px-3 text-center text-slate-400">{{ $prod->min_stok }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</x-app-layout>
