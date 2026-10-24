<x-app-layout>
    @section('title', 'Kasir')

    <div x-data="kasirApp({{ Js::from([
        'products' => $products,
        'taxEnabled' => (bool)$taxEnabled,
        'taxPercent' => (float)$taxPercent,
        'maxDiscount' => (float)$maxDiscount,
        'storeName' => \App\Models\Setting::getValue('store_name', 'Pos System'),
        'storeAddress' => \App\Models\Setting::getValue('store_address', '')
    ]) }})" x-init="init()" class="flex gap-6 h-[calc(100vh-120px)]">
        {{-- Left: Products --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Category Filter + Search --}}
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <div class="flex bg-white rounded-xl border border-slate-200 p-1 shadow-sm">
                    @foreach($categories as $cat)
                        <button @click="filterCategory = '{{ $cat }}'; fetchProducts()"
                                :class="filterCategory === '{{ $cat }}' ? 'bg-primary-600 text-white shadow-md shadow-primary-200' : 'text-slate-500 hover:bg-slate-50'"
                                class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-200 capitalize">
                            {{ $cat === 'semua' ? 'Semua' : ucfirst($cat) }}
                        </button>
                    @endforeach
                </div>
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchProducts()" placeholder="Cari nama atau kode barang..." class="input-field pl-10">
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    <template x-for="product in products" :key="product.id">
                        <button @click="addToCart(product)"
                                :class="product.stock <= 0 ? 'opacity-50 cursor-not-allowed' : 'hover:border-primary-500 hover:shadow-xl hover:-translate-y-1'"
                                :disabled="product.stock <= 0"
                                class="card p-3 text-left transition-all duration-300 border-2 border-transparent flex flex-col group">
                            
                            {{-- Product Image --}}
                            <div class="w-full h-32 mb-3 bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0 relative">
                                <template x-if="product.image">
                                    <img :src="'/storage/' + product.image" :alt="product.name" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                </template>
                                <template x-if="!product.image">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                                <div x-show="product.stock <= 0" class="absolute inset-0 bg-slate-900/60 flex items-center justify-center">
                                    <span class="bg-white text-slate-900 px-3 py-1 rounded-full text-[10px] font-bold uppercase">Habis</span>
                                </div>
                            </div>

                            <div class="flex items-start justify-between mb-2 w-full">
                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full"
                                      :class="product.category === 'makanan' ? 'bg-orange-100 text-orange-700' : product.category === 'minuman' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'"
                                      x-text="product.category.charAt(0).toUpperCase() + product.category.slice(1)"></span>
                                <span class="text-[10px] font-bold text-slate-400" x-text="product.code"></span>
                            </div>
                            <h3 class="text-sm font-bold text-slate-800 mb-1 line-clamp-2" x-text="product.name"></h3>
                            <p class="text-primary-600 font-extrabold text-base" x-text="formatRupiah(product.sell_price)"></p>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100">
                                <span class="text-xs font-bold" :class="product.stock <= product.min_stock ? 'text-rose-500' : 'text-slate-400'" x-text="'Stok: ' + product.stock"></span>
                                <span x-show="product.stock <= product.min_stock && product.stock > 0" class="text-[10px] bg-rose-100 text-rose-600 px-2 py-0.5 rounded-full font-bold uppercase">Low</span>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="products.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <p class="text-sm">Tidak ada produk ditemukan</p>
                </div>
            </div>
        </div>

        {{-- Right: Cart --}}
        <div class="w-96 flex flex-col card !rounded-3xl shadow-2xl border-none">
            <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-lg font-bold text-slate-800">🛒 Keranjang</h3>
                <p class="text-xs font-bold text-slate-400" x-text="cart.length + ' item'"></p>
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-white">
                <template x-for="(item, index) in cart" :key="item.product_id">
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100 transition-all duration-200">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 truncate" x-text="item.name"></h4>
                            <p class="text-xs text-primary-600 font-bold" x-text="formatRupiah(item.price)"></p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="decrementItem(index)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-700 text-sm font-bold hover:bg-slate-100 shadow-sm">−</button>
                            <span class="w-8 text-center text-sm font-extrabold text-slate-800" x-text="item.quantity"></span>
                            <button @click="incrementItem(index)" class="w-7 h-7 flex items-center justify-center rounded-lg bg-primary-600 text-white text-sm font-bold hover:bg-primary-700 shadow-lg shadow-primary-200">+</button>
                        </div>
                        <div class="text-right ml-2">
                            <p class="text-sm font-extrabold text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></p>
                            <button @click="removeItem(index)" class="text-[10px] font-bold text-rose-500 hover:text-rose-700 uppercase tracking-tighter">Hapus</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center py-20 text-slate-300">
                    <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.5 7.5M7 13L5.4 5M7 13h10m0 0l1.5 7.5M17 13l1.5 7.5m-13 0h13"/></svg>
                    <p class="text-sm font-bold">Keranjang kosong</p>
                </div>
            </div>

            {{-- Cart Summary --}}
            <div class="p-5 border-t border-slate-100 space-y-4 bg-slate-50/50">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-500 font-medium">
                        <span>Subtotal</span><span x-text="formatRupiah(subtotal)"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="text-slate-500 text-sm font-medium">Diskon (%)</label>
                        <input type="number" x-model.number="discountPercent" min="0" :max="maxDiscount" class="w-20 text-right input-field !py-1 !rounded-lg text-sm font-bold">
                    </div>
                    <div x-show="discountAmount > 0" class="flex justify-between text-rose-500 font-bold">
                        <span>Diskon</span><span x-text="'-' + formatRupiah(discountAmount)"></span>
                    </div>
                    <div x-show="taxEnabled" class="flex justify-between text-slate-500 font-medium">
                        <span x-text="'PPN ' + taxPercent + '%'"></span><span x-text="formatRupiah(taxAmount)"></span>
                    </div>
                    <div class="flex justify-between text-xl font-extrabold text-slate-900 pt-3 border-t border-slate-200">
                        <span>Total</span><span x-text="formatRupiah(total)" class="text-indigo-600"></span>
                    </div>
                </div>
                {{-- Payment Method --}}
                <div>
                    <label class="text-[10px] font-bold text-slate-400 mb-2 block uppercase tracking-widest">Metode Bayar</label>
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="method in ['tunai','qris','debit']" :key="method">
                            <button @click="paymentMethod = method"
                                    :class="paymentMethod === method ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-200' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-400 hover:bg-slate-50'"
                                    class="px-2 py-3 text-[10px] font-extrabold rounded-xl border-2 transition-all duration-200 capitalize" x-text="method.toUpperCase()"></button>
                        </template>
                    </div>
                </div>

                {{-- Paid Amount --}}
                <div x-show="paymentMethod === 'tunai'" class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                    <label class="text-[10px] font-bold text-slate-400 mb-2 block uppercase tracking-widest">Uang Diterima</label>
                    <input type="number" x-model.number="paidAmount" class="input-field !text-lg text-right font-black !bg-slate-50" placeholder="0">
                    <div x-show="changeAmount > 0" class="mt-2 flex justify-between items-center px-1">
                        <span class="text-xs font-bold text-slate-400 uppercase">Kembali</span>
                        <span class="text-lg font-black text-emerald-600" x-text="formatRupiah(changeAmount)"></span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-2 pt-2">
                    <button @click="clearCart()" class="btn-secondary flex-1" :disabled="cart.length === 0">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                    <button @click="submitTransaction()" class="btn-primary flex-1" :disabled="cart.length === 0 || isSubmitting">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span x-text="isSubmitting ? 'Memproses...' : 'Bayar & Cetak'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Receipt Modal --}}
        <div x-show="showReceipt" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showReceipt = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div id="receipt-content" class="p-8 text-center flex-1 overflow-y-auto" style="font-family: monospace; font-size: 12px;">
                    <h3 class="text-xl font-black text-slate-900 mb-1" x-text="storeName"></h3>
                    <p class="text-[10px] text-slate-500 mb-4" x-text="storeAddress"></p>
                    <hr class="border-dashed mb-3">
                    <div class="text-left text-xs space-y-1 mb-3">
                        <p>No: <span x-text="lastTransaction?.transaction_number"></span></p>
                        <p>Kasir: <span x-text="lastTransaction?.user?.name"></span></p>
                        <p>Tanggal: <span x-text="formatDate(lastTransaction?.created_at)"></span></p>
                    </div>
                    <hr class="border-dashed mb-3">
                    <template x-for="item in lastTransaction?.items || []" :key="item.id">
                        <div class="text-left text-xs mb-2">
                            <p class="font-semibold" x-text="item.product_name"></p>
                            <div class="flex justify-between">
                                <span x-text="item.quantity + ' x ' + formatRupiah(item.price)"></span>
                                <span x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </div>
                    </template>
                    <hr class="border-dashed my-3">
                    <div class="text-xs text-left space-y-1">
                        <div class="flex justify-between"><span>Subtotal</span><span x-text="formatRupiah(lastTransaction?.subtotal)"></span></div>
                        <div x-show="lastTransaction?.discount_amount > 0" class="flex justify-between"><span>Diskon</span><span x-text="'-' + formatRupiah(lastTransaction?.discount_amount)"></span></div>
                        <div x-show="lastTransaction?.tax_amount > 0" class="flex justify-between"><span x-text="'PPN ' + lastTransaction?.tax_percent + '%'"></span><span x-text="formatRupiah(lastTransaction?.tax_amount)"></span></div>
                        <div class="flex justify-between font-bold text-sm pt-1 border-t border-dashed"><span>TOTAL</span><span x-text="formatRupiah(lastTransaction?.total)"></span></div>
                        <div class="flex justify-between"><span x-text="lastTransaction?.payment_method?.toUpperCase()"></span><span x-text="formatRupiah(lastTransaction?.paid_amount)"></span></div>
                        <div x-show="lastTransaction?.change_amount > 0" class="flex justify-between"><span>Kembali</span><span x-text="formatRupiah(lastTransaction?.change_amount)"></span></div>
                    </div>
                    <hr class="border-dashed my-3">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Terima kasih!</p>
                </div>
                <div class="flex gap-3 p-6 bg-slate-50 border-t border-slate-100">
                    <button @click="showReceipt = false" class="btn-secondary flex-1 !py-3">Tutup</button>
                    <button @click="printReceipt()" class="btn-primary flex-1 !py-3 shadow-primary-200">🖨 Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function kasirApp(config) {
        return {
            products: config.products,
            cart: [],
            filterCategory: 'semua',
            searchQuery: '',
            discountPercent: 0,
            taxEnabled: config.taxEnabled,
            taxPercent: config.taxPercent,
            maxDiscount: config.maxDiscount,
            paymentMethod: 'tunai',
            paidAmount: 0,
            isSubmitting: false,
            showReceipt: false,
            lastTransaction: null,
            storeName: config.storeName,
            storeAddress: config.storeAddress,

            init() { this.fetchProducts(); },

            async fetchProducts() {
                const params = new URLSearchParams();
                if (this.filterCategory !== 'semua') params.append('category', this.filterCategory);
                if (this.searchQuery) params.append('search', this.searchQuery);
                const res = await fetch(`{{ route('kasir.products') }}?${params}`);
                this.products = await res.json();
            },

            addToCart(product) {
                if (product.stock <= 0) return;
                const existing = this.cart.find(i => i.product_id === product.id);
                if (existing) {
                    if (existing.quantity < product.stock) existing.quantity++;
                } else {
                    this.cart.push({ product_id: product.id, name: product.name, price: Number(product.sell_price), quantity: 1, stock: product.stock });
                }
            },

            incrementItem(i) { if (this.cart[i].quantity < this.cart[i].stock) this.cart[i].quantity++; },
            decrementItem(i) { if (this.cart[i].quantity > 1) this.cart[i].quantity--; else this.cart.splice(i, 1); },
            removeItem(i) { this.cart.splice(i, 1); },
            clearCart() { this.cart = []; this.discountPercent = 0; this.paidAmount = 0; },

            get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.quantity, 0); },
            get discountAmount() { return Math.round(this.subtotal * this.discountPercent / 100); },
            get afterDiscount() { return this.subtotal - this.discountAmount; },
            get taxAmount() { return this.taxEnabled ? Math.round(this.afterDiscount * this.taxPercent / 100) : 0; },
            get total() { return this.afterDiscount + this.taxAmount; },
            get changeAmount() { return this.paymentMethod === 'tunai' ? Math.max(0, this.paidAmount - this.total) : 0; },

            formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); },
            formatDate(d) { return d ? new Date(d).toLocaleString('id-ID') : ''; },

            async submitTransaction() {
                if (this.cart.length === 0) return;
                if (this.discountPercent < 0 || this.discountPercent > this.maxDiscount) {
                    alert(`Diskon harus antara 0 dan ${this.maxDiscount}%.`); return;
                }
                if (this.paymentMethod === 'tunai' && this.paidAmount < this.total) {
                    alert('Uang yang diterima kurang!'); return;
                }
                this.isSubmitting = true;
                const paid = this.paymentMethod === 'tunai' ? this.paidAmount : this.total;
                try {
                    const res = await fetch('{{ route("kasir.store") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            items: this.cart.map(i => ({ product_id: i.product_id, quantity: i.quantity })),
                            discount_percent: this.discountPercent || 0,
                            payment_method: this.paymentMethod,
                            paid_amount: paid,
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.lastTransaction = data.transaction;
                        this.showReceipt = true;
                        this.clearCart();
                        this.fetchProducts();
                    } else {
                        alert(data.message || 'Gagal menyimpan transaksi');
                    }
                } catch (e) { alert('Terjadi kesalahan'); }
                this.isSubmitting = false;
            },

            printReceipt() {
                const content = document.getElementById('receipt-content').innerHTML;
                const w = window.open('', '_blank', 'width=350,height=600');
                w.document.write(`<html><head><title>Struk</title><style>body{font-family:monospace;font-size:12px;padding:10px;} hr{border-style:dashed;}</style></head><body>${content}</body></html>`);
                w.document.close(); w.print(); w.close();
            }
        }
    }
    </script>
</x-app-layout>
