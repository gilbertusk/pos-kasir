<x-app-layout>
    @section('title', 'Barang & Stok')
    <div x-data="{ showAddModal: false, showEditModal: false, editProduct: {}, showStockModal: false, stockProduct: {}, stockType: 'masuk' }">
        {{-- Header Actions --}}
        <div class="flex items-center justify-between mb-8">
            <div></div>
            <div class="flex gap-3">
                <a href="{{ route('products.stockHistory') }}" class="btn-secondary !rounded-xl !py-3 shadow-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>Riwayat Stok</a>
                <a href="{{ route('products.exportPdf') }}" class="btn-secondary !rounded-xl !py-3 shadow-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Ekspor PDF</a>
                <button @click="showAddModal = true" class="btn-primary !rounded-xl !py-3 shadow-primary-200"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah Barang</button>
            </div>
        </div>

        {{-- Low Stock Alert --}}
        @if($lowStockCount > 0)
        <div class="flex items-center gap-3 p-4 mb-6 bg-kunyit-50 border border-kunyit-200 rounded-2xl text-kunyit-800 text-sm font-bold shadow-sm">
            <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm text-kunyit-700">
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
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Kode</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Nama barang</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Kategori</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Satuan</th>
                            <th class="px-4 py-4 text-right font-medium text-slate-500 text-xs">Harga beli</th>
                            <th class="px-4 py-4 text-right font-medium text-slate-500 text-xs">Harga jual</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Stok</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Min</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Status</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                        <tr class="hover:bg-slate-50 transition-all duration-200 group">
                            <td class="whitespace-nowrap px-4 py-4 font-mono text-xs font-medium text-slate-400">{{ $product->code }}</td>
                            <td class="px-4 py-4 font-bold text-slate-800">{{ $product->name }}</td>
                            <td class="px-4 py-4"><span class="px-3 py-1 text-xs font-bold rounded-full {{ $product->category === 'makanan' ? 'bg-kunyit-100 text-kunyit-800' : ($product->category === 'minuman' ? 'bg-brand-50 text-brand-800' : 'bg-enamel-100 text-enamel-700') }}">{{ ucfirst($product->category) }}</span></td>
                            <td class="px-4 py-4 text-slate-500 font-medium">{{ $product->unit }}</td>
                            <td class="whitespace-nowrap tabular px-4 py-4 text-right text-slate-400 font-medium">{{ formatRupiah($product->buy_price) }}</td>
                            <td class="whitespace-nowrap tabular px-4 py-4 text-right font-bold text-slate-900">{{ formatRupiah($product->sell_price) }}</td>
                            <td class="tabular px-4 py-4 text-center {{ $product->isLowStock() ? 'text-kunyit-800 font-bold' : 'text-slate-700 font-bold' }}">{{ $product->stock }}</td>
                            <td class="tabular px-4 py-4 text-center text-slate-400 font-bold">{{ $product->min_stock }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $product->is_active ? 'bg-brand-100 text-brand-800' : 'bg-enamel-100 text-enamel-600' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="stockProduct = {{ Js::from($product->only('id', 'name', 'stock', 'unit')) }}; stockType = 'masuk'; showStockModal = true" class="p-1.5 rounded-lg hover:bg-brand-50 text-brand-700" title="Ubah stok" aria-label="Ubah stok {{ $product->name }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg></button>
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
                    <h3 class="text-lg font-bold text-slate-800">Tambah Barang Baru</h3>
                    <button @click="showAddModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Kode *</label><input type="text" name="code" required class="input-field"></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Satuan *</label><input type="text" name="unit" value="Porsi" required class="input-field"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Nama barang *</label><input type="text" name="name" required class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Kategori *</label><select name="category" required class="input-field"><option value="makanan">Makanan</option><option value="minuman">Minuman</option><option value="lainnya">Lainnya</option></select></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Deskripsi</label><input type="text" name="description" class="input-field"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Gambar produk</label><input type="file" name="image" accept="image/*" class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Harga beli *</label><input type="number" name="buy_price" min="0" required class="input-field"></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Harga jual *</label><input type="number" name="sell_price" min="0" required class="input-field"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Stok *</label><input type="number" name="stock" min="0" required class="input-field"></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Min. stok *</label><input type="number" name="min_stock" min="0" value="5" required class="input-field"></div>
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
                    <h3 class="text-lg font-bold text-slate-800">Edit Barang</h3>
                    <button @click="showEditModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form :action="'/products/' + editProduct.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Kode *</label><input type="text" name="code" :value="editProduct.code" required class="input-field"></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Satuan *</label><input type="text" name="unit" :value="editProduct.unit" required class="input-field"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Nama barang *</label><input type="text" name="name" :value="editProduct.name" required class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Kategori *</label><select name="category" required class="input-field"><option value="makanan" :selected="editProduct.category === 'makanan'">Makanan</option><option value="minuman" :selected="editProduct.category === 'minuman'">Minuman</option><option value="lainnya" :selected="editProduct.category === 'lainnya'">Lainnya</option></select></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Deskripsi</label><input type="text" name="description" :value="editProduct.description" class="input-field"></div>
                    </div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Gambar produk</label><input type="file" name="image" accept="image/*" class="input-field"></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Harga beli *</label><input type="number" name="buy_price" :value="editProduct.buy_price" min="0" required class="input-field"></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Harga jual *</label><input type="number" name="sell_price" :value="editProduct.sell_price" min="0" required class="input-field"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs font-medium text-slate-400 mb-1">Stok saat ini</span>
                            <p class="input-field bg-slate-50 tabular" x-text="editProduct.stock"></p>
                            <p class="mt-1 text-xs text-slate-400">Ubah lewat tombol stok supaya tercatat di riwayat.</p>
                        </div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Min. stok *</label><input type="number" name="min_stock" :value="editProduct.min_stock" min="0" required class="input-field"></div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Stock Modal --}}
        <div x-show="showStockModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showStockModal = false" @keydown.escape.window="showStockModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col" role="dialog" aria-modal="true" aria-labelledby="stock-modal-title">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div>
                        <h3 id="stock-modal-title" class="text-lg font-bold text-slate-800">Ubah Stok</h3>
                        <p class="text-sm text-slate-500"><span x-text="stockProduct.name"></span> · stok sekarang <span class="tabular font-bold text-slate-800" x-text="stockProduct.stock"></span> <span x-text="stockProduct.unit"></span></p>
                    </div>
                    <button type="button" @click="showStockModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors" aria-label="Tutup"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form :action="'/products/' + stockProduct.id + '/stock'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <fieldset class="grid grid-cols-2 gap-2">
                        <legend class="sr-only">Jenis perubahan</legend>
                        <label class="cursor-pointer rounded-xl border p-3 text-sm" :class="stockType === 'masuk' ? 'border-brand-600 bg-brand-50 text-brand-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" name="type" value="masuk" x-model="stockType" class="sr-only">
                            <span class="block font-bold">Stok masuk</span>
                            <span class="text-xs">Tambah dari belanja/supplier</span>
                        </label>
                        <label class="cursor-pointer rounded-xl border p-3 text-sm" :class="stockType === 'penyesuaian' ? 'border-brand-600 bg-brand-50 text-brand-800' : 'border-slate-200 text-slate-600'">
                            <input type="radio" name="type" value="penyesuaian" x-model="stockType" class="sr-only">
                            <span class="block font-bold">Penyesuaian</span>
                            <span class="text-xs">Samakan dengan hasil hitung</span>
                        </label>
                    </fieldset>
                    <div x-show="stockType === 'masuk'">
                        <label for="stock-quantity" class="block text-xs font-medium text-slate-400 mb-1">Jumlah masuk *</label>
                        <input id="stock-quantity" type="number" name="quantity" min="1" class="input-field" :required="stockType === 'masuk'" :disabled="stockType !== 'masuk'">
                    </div>
                    <div x-show="stockType === 'penyesuaian'" x-cloak>
                        <label for="stock-actual" class="block text-xs font-medium text-slate-400 mb-1">Stok hasil hitung *</label>
                        <input id="stock-actual" type="number" name="actual_stock" min="0" class="input-field" :required="stockType === 'penyesuaian'" :disabled="stockType !== 'penyesuaian'">
                    </div>
                    <div>
                        <label for="stock-note" class="block text-xs font-medium text-slate-400 mb-1">
                            <span x-text="stockType === 'penyesuaian' ? 'Alasan *' : 'Catatan'"></span>
                        </label>
                        <input id="stock-note" type="text" name="note" maxlength="255" class="input-field" :required="stockType === 'penyesuaian'" :placeholder="stockType === 'penyesuaian' ? 'Misalnya: 3 bungkus rusak' : 'Misalnya: belanja pasar, supplier'">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showStockModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan Stok</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>

    {{-- AI Assistant Chat Panel --}}
    <div x-data="aiAssistant()" class="fixed right-0 bottom-0 sm:right-6 sm:bottom-6 z-50 flex flex-col items-end w-full sm:w-auto">
        {{-- Chat Panel --}}
        <div x-show="isOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" class="bg-white sm:rounded-2xl shadow-2xl border border-slate-100 w-full sm:w-[380px] mb-0 sm:mb-4 overflow-hidden flex flex-col h-[100vh] sm:h-[75vh] max-h-[700px]" style="display: none;">
            
            {{-- Header --}}
            <div class="bg-brand-800 p-3.5 flex items-center justify-between text-white shrink-0 shadow-sm z-10">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"></path></svg>
                    <h3 class="font-bold text-sm">AI Kasir Assistant</h3>
                </div>
                <button @click="isOpen = false" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Messages Area --}}
            <div class="flex-1 p-4 overflow-y-auto bg-slate-50 space-y-5" id="ai-messages-container">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user' ? 'bg-primary-600 text-white rounded-2xl rounded-tr-sm max-w-[80%]' : (msg.isError ? 'bg-rose-100 text-rose-700 rounded-2xl rounded-tl-sm max-w-[85%]' : 'bg-white border border-slate-200 text-slate-700 rounded-2xl rounded-tl-sm max-w-[85%]')" class="p-2.5 shadow-sm text-[13px] leading-relaxed">
                            <div x-html="msg.role === 'ai' ? formatText(msg.text) : escapeHtml(msg.text)" class="ai-content"></div>
                        </div>
                    </div>
                </template>
                <div x-show="isLoading" class="flex justify-start" style="display: none;">
                    <div class="bg-white border border-slate-200 text-slate-500 rounded-2xl rounded-tl-sm max-w-[85%] p-2.5 text-[13px] shadow-sm flex items-center gap-3">
                        <div class="flex space-x-1">
                            <div class="w-1.5 h-1.5 bg-primary-400 rounded-full animate-bounce" style="animation-delay: -0.3s"></div>
                            <div class="w-1.5 h-1.5 bg-primary-400 rounded-full animate-bounce" style="animation-delay: -0.15s"></div>
                            <div class="w-1.5 h-1.5 bg-primary-400 rounded-full animate-bounce"></div>
                        </div>
                        <span class="text-xs font-medium">AI menganalisis...</span>
                    </div>
                </div>
            </div>

            {{-- Example Prompts --}}
            <div class="p-2.5 bg-white border-t border-slate-100 overflow-x-auto whitespace-nowrap scrollbar-hide shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] z-10">
                <div class="flex gap-2">
                    <button @click="setInput('Produk apa yang stoknya kritis?')" class="text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">Stok kritis?</button>
                    <button @click="setInput('Produk apa yang harus direstock?')" class="text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">Perlu restock?</button>
                    <button @click="setInput('Apakah ada produk yang stoknya habis?')" class="text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">Stok habis?</button>
                    <button @click="setInput('Buatkan ringkasan kondisi stok saat ini.')" class="text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-full transition-colors border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1">Ringkasan stok</button>
                </div>
            </div>

            {{-- Input Area --}}
            <div class="p-3 bg-white border-t border-slate-100 shrink-0">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input type="text" x-ref="inputField" x-model="input" placeholder="Tanya AI..." class="flex-1 input-field !py-2 !px-3 !rounded-full !text-[13px] focus:ring-primary-500 focus:border-primary-500" :disabled="isLoading">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white w-9 h-9 flex items-center justify-center rounded-full transition-colors shadow-sm disabled:opacity-50 shrink-0 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2" :disabled="isLoading || input.trim() === ''">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Floating Button Desktop --}}
        <button x-show="!isOpen" @click="isOpen = true" x-transition class="bg-brand-800 text-white rounded-full px-4 py-2.5 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all transform items-center gap-2 mb-6 mr-6 hidden sm:flex focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"></path></svg>
            <span class="font-bold text-sm tracking-wide">AI Assistant</span>
        </button>
        
        {{-- Floating Button Mobile --}}
        <button x-show="!isOpen" @click="isOpen = true" x-transition class="bg-brand-800 text-white rounded-full p-3 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all transform items-center justify-center mb-6 mr-6 flex sm:hidden focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"></path></svg>
        </button>
    </div>

    <style>
        .ai-content p { margin-bottom: 0.5rem; }
        .ai-content p:last-child { margin-bottom: 0; }
        .ai-content ul { list-style-type: disc; padding-left: 1.25rem; margin-bottom: 0.5rem; margin-top: 0.25rem; }
        .ai-content ol { list-style-type: decimal; padding-left: 1.25rem; margin-bottom: 0.5rem; margin-top: 0.25rem; }
        .ai-content li { margin-bottom: 0.25rem; }
        .ai-content strong { font-weight: 700; color: #1B2620; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('aiAssistant', () => ({
                isOpen: false,
                input: '',
                isLoading: false,
                messages: [
                    { role: 'ai', text: 'Halo! Saya AI Kasir Assistant.\n\nAda yang bisa saya bantu terkait stok, rekomendasi restock, atau ringkasan POS hari ini?', isError: false }
                ],
                init() {
                    this.$watch('messages', () => {
                        this.$nextTick(() => {
                            const container = document.getElementById('ai-messages-container');
                            container.scrollTop = container.scrollHeight;
                        });
                    });
                },
                escapeHtml(unsafe) {
                    return unsafe
                         .replace(/&/g, "&amp;")
                         .replace(/</g, "&lt;")
                         .replace(/>/g, "&gt;")
                         .replace(/"/g, "&quot;")
                         .replace(/'/g, "&#039;");
                },
                formatText(text) {
                    let html = this.escapeHtml(text);
                    
                    // Replace bold **text**
                    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    
                    let lines = html.split('\n');
                    let inList = false;
                    let listType = '';
                    let result = [];
                    
                    for (let i = 0; i < lines.length; i++) {
                        let line = lines[i];
                        let isList = false;
                        
                        if (line.match(/^(?:-|\*)\s+(.*)$/)) {
                            if (!inList || listType !== 'ul') {
                                if (inList) result.push(`</${listType}>`);
                                result.push('<ul>');
                                inList = true;
                                listType = 'ul';
                            }
                            result.push('<li>' + line.replace(/^(?:-|\*)\s+(.*)$/, '$1') + '</li>');
                            isList = true;
                        } else if (line.match(/^(\d+)\.\s+(.*)$/)) {
                            if (!inList || listType !== 'ol') {
                                if (inList) result.push(`</${listType}>`);
                                result.push('<ol>');
                                inList = true;
                                listType = 'ol';
                            }
                            result.push('<li>' + line.replace(/^(\d+)\.\s+(.*)$/, '$2') + '</li>');
                            isList = true;
                        }
                        
                        if (!isList) {
                            if (inList) {
                                result.push(`</${listType}>`);
                                inList = false;
                            }
                            result.push(line);
                        }
                    }
                    if (inList) {
                        result.push(`</${listType}>`);
                    }
                    
                    let finalHtml = '';
                    for (let i = 0; i < result.length; i++) {
                        let line = result[i];
                        if (line.startsWith('<ul') || line.startsWith('</ul') || 
                            line.startsWith('<ol') || line.startsWith('</ol') || 
                            line.startsWith('<li')) {
                            finalHtml += line;
                        } else {
                            finalHtml += line + (i < result.length - 1 ? '<br>' : '');
                        }
                    }
                    return finalHtml;
                },
                setInput(text) {
                    this.input = text;
                    this.$refs.inputField?.focus();
                },
                async sendMessage() {
                    if (this.input.trim() === '' || this.isLoading) return;
                    
                    const userText = this.input.trim();
                    this.messages.push({ role: 'user', text: userText, isError: false });
                    this.input = '';
                    this.isLoading = true;

                    try {
                        const response = await fetch('/ai/chat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ message: userText })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages.push({ role: 'ai', text: data.reply, isError: false });
                        } else {
                            this.messages.push({ role: 'ai', text: data.message || 'Terjadi kesalahan.', isError: true });
                        }
                    } catch (error) {
                        this.messages.push({ role: 'ai', text: 'Gagal terhubung ke server. Periksa koneksi internet.', isError: true });
                    } finally {
                        this.isLoading = false;
                        this.$nextTick(() => {
                            this.$refs.inputField?.focus();
                        });
                    }
                }
            }));
        });
    </script>
</x-app-layout>
