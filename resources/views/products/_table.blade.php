<!-- Mobile: card list -->
<div class="md:hidden divide-y divide-slate-100">
    @forelse($products as $product)
        <div class="p-4 hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-slate-700 truncate">{{ $product->name }}</p>
                    <p class="font-mono text-xs text-slate-400 mt-0.5">{{ $product->sku }} ·
                        {{ $product->category->name }}</p>
                </div>
                <span
                    class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
            </div>
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    <p>Beli: <span class="font-semibold text-slate-700">Rp
                            {{ number_format($product->harga_beli, 0, ',', '.') }}</span></p>
                    <p>Jual: <span class="font-semibold text-slate-700">Rp
                            {{ number_format($product->harga_jual, 0, ',', '.') }}</span></p>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('products.show', $product) }}"
                        class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                        title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg></a>
                    <a href="{{ route('products.edit', $product) }}"
                        class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                        title="Edit"><svg
                            class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg></a>
                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                        onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                            title="Hapus"><svg
                                class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg></button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="py-12 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-sm font-medium text-slate-500">Tidak ada produk yang ditemukan.</p>
            <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian atau kategori lain.</p>
        </div>
    @endforelse
</div>

<!-- Desktop: table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">#</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Produk</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">SKU</th>
                <th class="text-left py-3 px-4 font-semibold text-slate-600">Kategori</th>
                <th class="text-right py-3 px-4 font-semibold text-slate-600">Harga Beli</th>
                <th class="text-right py-3 px-4 font-semibold text-slate-600">Harga Jual</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Stok</th>
                <th class="text-center py-3 px-4 font-semibold text-slate-600">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($products as $i => $product)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="py-3 px-4 text-slate-500">{{ $products->firstItem() + $i }}</td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span class="font-medium text-slate-700">{{ $product->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-slate-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $product->category->name }}</td>
                    <td class="py-3 px-4 text-right text-slate-600">Rp
                        {{ number_format($product->harga_beli, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-700">Rp
                        {{ number_format($product->harga_jual, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-center">
                        <span
                            class="px-2 py-0.5 rounded-full text-xs font-bold {{ $product->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $product->stok }}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('products.show', $product) }}"
                                class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="{{ route('products.edit', $product) }}"
                                class="p-1.5 rounded-lg text-slate-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                onsubmit="return confirmForm(this, 'Yakin hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                                    title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-12 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <p class="text-sm font-medium text-slate-500">Tidak ada produk yang ditemukan.</p>
                        <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian atau kategori lain.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-3 border-t border-slate-100">{{ $products->appends(request()->query())->links() }}</div>
