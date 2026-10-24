<x-app-layout>
    @section('title', 'Barang & Stok')
    <div x-data="{ showAddModal: false, showEditModal: false, editProduct: {} }">
        {{-- Header Actions --}}
        <div class="flex items-center justify-between mb-8">
            <div></div>
            <div class="flex gap-3">
                <a href="{{ route('products.exportPdf') }}" class="btn-secondary !rounded-xl !py-3 shadow-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Ekspor PDF</a>
                <button @click="showAddModal = true" class="btn-primary !rounded-xl !py-3 shadow-primary-200"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah Barang</button>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        @if($lowStockCount > 0)
        <div class="flex items-center gap-3 p-4 mb-6 bg-rose-50 border border-rose-100 rounded-2xl text-rose-700 text-sm font-bold shadow-sm">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm text-rose-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <span>{{ $lowStockCount }} barang memiliki stok di bawah minimum! Mohon segera restok.</span>
        </div>
        @endif

        {{-- Filters --}}
        <div class="card p-4 mb-4">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="category" class="input-field w-auto" onchange="this.form.submit()">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat === 'semua' ? 'Semua kategori' : ucfirst($cat) }}</option>
                    @endforeach
                </select>
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode barang..." class="input-field pl-10">
                </div>
                <button type="submit" class="btn-primary">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Kode</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Nama Barang</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Kategori</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Satuan</th>
                            <th class="px-4 py-4 text-right font-bold text-slate-500 uppercase text-[10px] tracking-wider">Harga Beli</th>
                            <th class="px-4 py-4 text-right font-bold text-slate-500 uppercase text-[10px] tracking-wider">Harga Jual</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Stok</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Min</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Status</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition-all duration-200 group">
                            <td class="px-4 py-4 font-mono text-xs font-bold text-slate-400">{{ $product->code }}</td>
                            <td class="px-4 py-4 font-bold text-slate-800">{{ $product->name }}</td>
                            <td class="px-4 py-4"><span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ $product->category === 'makanan' ? 'bg-orange-100 text-orange-700' : ($product->category === 'minuman' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700') }}">{{ $product->category }}</span></td>
                            <td class="px-4 py-4 text-slate-500 font-medium">{{ $product->unit }}</td>
                            <td class="px-4 py-4 text-right text-slate-400 font-medium">{{ formatRupiah($product->buy_price) }}</td>
                            <td class="px-4 py-4 text-right font-black text-slate-900">{{ formatRupiah($product->sell_price) }}</td>
                            <td class="px-4 py-4 text-center {{ $product->isLowStock() ? 'text-rose-600 font-black' : 'text-slate-700 font-bold' }}">{{ $product->stock }}</td>
                            <td class="px-4 py-4 text-center text-slate-400 font-bold">{{ $product->min_stock }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ $product->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editProduct = {{ $product->toJson() }}; showEditModal = true" class="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 text-blue-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <form method="POST" action="{{ route('products.toggle', $product) }}">@csrf @method('PATCH')
                                        <button class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600" title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }} {{ $product->name }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 11-12.728 0M12 3v9"/></svg></button>
                                    </form>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus barang ini?')">@csrf @method('DELETE')
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10" class="px-4 py-12 text-center text-gray-400">Belum ada data barang</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $products->withQueryString()->links() }}</div>
        </div>

        {{-- Add Modal --}}
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showAddModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-800">Tambah Barang Baru</h3>
                    <button @click="showAddModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kode *</label><input type="text" name="code" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Satuan *</label><input type="text" name="unit" value="Porsi" required class="input-field"></div>
                    </div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Barang *</label><input type="text" name="name" required class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori *</label><select name="category" required class="input-field"><option value="makanan">Makanan</option><option value="minuman">Minuman</option><option value="lainnya">Lainnya</option></select></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Deskripsi</label><input type="text" name="description" class="input-field"></div>
                    </div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gambar Produk</label><input type="file" name="image" accept="image/*" class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Beli *</label><input type="number" name="buy_price" min="0" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Jual *</label><input type="number" name="sell_price" min="0" required class="input-field"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Stok *</label><input type="number" name="stock" min="0" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Min. Stok *</label><input type="number" name="min_stock" min="0" value="5" required class="input-field"></div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showAddModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showEditModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-800">Edit Barang</h3>
                    <button @click="showEditModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form :action="'/products/' + editProduct.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kode *</label><input type="text" name="code" :value="editProduct.code" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Satuan *</label><input type="text" name="unit" :value="editProduct.unit" required class="input-field"></div>
                    </div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nama Barang *</label><input type="text" name="name" :value="editProduct.name" required class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori *</label><select name="category" required class="input-field"><option value="makanan" :selected="editProduct.category === 'makanan'">Makanan</option><option value="minuman" :selected="editProduct.category === 'minuman'">Minuman</option><option value="lainnya" :selected="editProduct.category === 'lainnya'">Lainnya</option></select></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Deskripsi</label><input type="text" name="description" :value="editProduct.description" class="input-field"></div>
                    </div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gambar Produk</label><input type="file" name="image" accept="image/*" class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Beli *</label><input type="number" name="buy_price" :value="editProduct.buy_price" min="0" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Jual *</label><input type="number" name="sell_price" :value="editProduct.sell_price" min="0" required class="input-field"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Stok *</label><input type="number" name="stock" :value="editProduct.stock" min="0" required class="input-field"></div>
                        <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Min. Stok *</label><input type="number" name="min_stock" :value="editProduct.min_stock" min="0" required class="input-field"></div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
