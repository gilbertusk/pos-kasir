<x-app-layout>
    @section('title', 'Kasir')
    @section('mainClass', 'p-0')

    <div x-data="kasirApp({{ Js::from([
        'products' => $products,
        'taxEnabled' => (bool)$taxEnabled,
        'taxPercent' => (float)$taxPercent,
        'maxDiscount' => (float)$maxDiscount,
        'storeName' => \App\Models\Setting::getValue('store_name', config('app.name')),
        'storeAddress' => \App\Models\Setting::getValue('store_address', '')
    ]) }})" x-init="init()"
         class="lg:grid lg:h-[calc(100dvh-4rem)] lg:grid-cols-[minmax(0,1fr)_380px] lg:grid-rows-[minmax(0,1fr)]">

        {{-- Products --}}
        <section class="flex min-w-0 flex-col lg:min-h-0" aria-label="Daftar menu">
            <div class="sticky top-16 z-20 space-y-3 border-b border-enamel-200 bg-enamel-50/95 px-4 py-3 backdrop-blur md:px-6 lg:static">
                <label class="relative block">
                    <span class="sr-only">Cari menu</span>
                    <svg class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-enamel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" x-model="searchQuery" @input.debounce.300ms="fetchProducts()" placeholder="Cari nama atau kode menu" class="input-field min-h-[44px] pl-11">
                </label>
                <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-0.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="Kategori">
                    @foreach($categories as $cat)
                        <button type="button" @click="filterCategory = '{{ $cat }}'; fetchProducts()"
                                :aria-pressed="(filterCategory === '{{ $cat }}').toString()"
                                :class="filterCategory === '{{ $cat }}' ? 'border-brand-800 bg-brand-800 text-white' : 'border-enamel-200 bg-white text-enamel-600 hover:border-enamel-300 hover:text-ink'"
                                class="min-h-[40px] flex-shrink-0 rounded-full border px-4 text-sm font-medium transition-colors focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-200">
                            {{ $cat === 'semua' ? 'Semua' : ucfirst($cat) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex-1 px-4 pb-28 pt-4 md:px-6 lg:min-h-0 lg:overflow-y-auto lg:pb-6">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                    <template x-for="product in products" :key="product.id">
                        <button type="button" @click="addToCart(product)"
                                :disabled="product.stock <= 0"
                                :aria-label="product.name + ', ' + formatRupiah(product.sell_price) + (product.stock <= 0 ? ', habis' : '')"
                                :class="qtyInCart(product.id) > 0 ? 'border-brand-600 ring-2 ring-brand-600/20' : 'border-enamel-200 hover:border-enamel-300'"
                                class="group relative flex flex-col overflow-hidden rounded-2xl border bg-white text-left transition-[border-color,box-shadow,transform] focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:active:scale-100">
                            <div class="relative aspect-[4/3] w-full bg-enamel-100">
                                <template x-if="product.image">
                                    <img :src="'/storage/' + product.image" alt="" loading="lazy" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!product.image">
                                    <div class="flex h-full items-center justify-center text-enamel-300">
                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                                <span x-show="qtyInCart(product.id) > 0" x-text="qtyInCart(product.id)"
                                      class="tabular absolute right-2 top-2 flex h-8 min-w-[32px] items-center justify-center rounded-full bg-brand-800 px-2 text-sm font-bold text-white ring-2 ring-white"></span>
                                <div x-show="product.stock <= 0" class="absolute inset-0 flex items-center justify-center bg-white/70">
                                    <span class="rounded-full bg-sambal-700 px-3 py-1 text-xs font-semibold text-white">Habis</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col gap-1 p-3">
                                <h3 class="line-clamp-2 text-sm font-medium leading-snug text-ink" x-text="product.name"></h3>
                                <p class="tabular mt-auto text-base font-bold text-ink" x-text="formatRupiah(product.sell_price)"></p>
                                <p class="text-xs" :class="product.stock > 0 && product.stock <= product.min_stock ? 'font-medium text-kunyit-800' : 'text-enamel-400'">
                                    <span x-show="product.stock > 0 && product.stock <= product.min_stock">Sisa </span>
                                    <span x-show="product.stock > product.min_stock">Stok </span>
                                    <span class="tabular" x-show="product.stock > 0" x-text="product.stock"></span>
                                    <span x-show="product.stock <= 0">Tidak tersedia</span>
                                </p>
                            </div>
                        </button>
                    </template>
                </div>
                <div x-show="products.length === 0" x-cloak class="flex flex-col items-center justify-center py-20 text-center">
                    <p class="text-base font-medium text-ink">Menu tidak ditemukan</p>
                    <p class="mt-1 text-sm text-enamel-500">Coba kata kunci lain atau pilih kategori Semua.</p>
                </div>
            </div>
        </section>

        {{-- Mobile/tablet order bar --}}
        <div x-show="cart.length > 0" x-transition.opacity class="fixed inset-x-0 bottom-0 z-30 border-t border-enamel-200 bg-white px-4 py-3 lg:hidden">
            <button type="button" @click="showCart = true" class="flex min-h-[52px] w-full items-center justify-between gap-3 rounded-xl bg-brand-800 px-4 text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-300">
                <span class="text-sm"><span class="tabular font-semibold" x-text="itemCount"></span> item</span>
                <span class="flex items-center gap-2 font-semibold">
                    <span class="tabular" x-text="formatRupiah(total)"></span>
                    <span class="text-brand-200" aria-hidden="true">›</span>
                    <span class="sr-only">Lihat pesanan</span>
                </span>
            </button>
        </div>

        {{-- Order panel: a column on large screens, a bottom sheet below that --}}
        <div x-show="showCart" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-ink/50 lg:hidden" @click="showCart = false"></div>
        <aside :class="showCart ? 'translate-y-0' : 'translate-y-full lg:translate-y-0'"
               class="fixed inset-x-0 bottom-0 z-50 flex max-h-[88dvh] flex-col rounded-t-2xl bg-white shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:max-h-none lg:min-h-0 lg:translate-y-0 lg:rounded-none lg:border-l lg:border-enamel-200 lg:shadow-none"
               aria-label="Pesanan">
            <div class="flex items-center justify-between gap-3 border-b border-enamel-200 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-ink">Pesanan</h2>
                    <p class="text-sm text-enamel-500"><span class="tabular" x-text="itemCount"></span> item</p>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" x-show="cart.length > 0" @click="clearCart()" class="min-h-[40px] rounded-lg px-3 text-sm font-medium text-sambal-700 hover:bg-sambal-50">Kosongkan</button>
                    <button type="button" @click="showCart = false" class="rounded-lg p-2 text-enamel-500 hover:bg-enamel-100 lg:hidden" aria-label="Tutup pesanan">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Items --}}
            <div class="min-h-[96px] flex-1 overflow-y-auto px-5">
                <ul class="divide-y divide-enamel-100">
                    <template x-for="(item, index) in cart" :key="item.product_id">
                        <li class="flex items-center gap-3 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-ink" x-text="item.name"></p>
                                <p class="tabular text-sm text-enamel-500" x-text="formatRupiah(item.price * item.quantity)"></p>
                            </div>
                            <div class="flex items-center rounded-xl border border-enamel-200">
                                <button type="button" @click="decrementItem(index)" class="flex h-11 w-11 items-center justify-center rounded-l-xl text-lg text-enamel-700 hover:bg-enamel-50" :aria-label="'Kurangi ' + item.name">−</button>
                                <span class="tabular w-8 text-center text-sm font-semibold text-ink" x-text="item.quantity"></span>
                                <button type="button" @click="incrementItem(index)" :disabled="item.quantity >= item.stock" class="flex h-11 w-11 items-center justify-center rounded-r-xl text-lg text-brand-800 hover:bg-brand-50 disabled:cursor-not-allowed disabled:text-enamel-300" :aria-label="'Tambah ' + item.name">+</button>
                            </div>
                        </li>
                    </template>
                </ul>
                <div x-show="cart.length === 0" class="py-10 text-center">
                    <p class="text-sm font-medium text-ink">Belum ada pesanan</p>
                    <p class="mt-1 text-sm text-enamel-500">Ketuk menu untuk menambahkannya.</p>
                </div>
            </div>

            {{-- Totals and payment --}}
            <div class="space-y-3 border-t border-enamel-200 bg-enamel-50 px-5 py-4">
                <dl class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-enamel-500">
                        <dt>Subtotal</dt><dd class="tabular" x-text="formatRupiah(subtotal)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-enamel-500">
                        <dt><label for="discount">Diskon (%)</label></dt>
                        <dd class="flex items-center gap-2">
                            <span x-show="discountAmount > 0" class="tabular" x-text="'−' + formatRupiah(discountAmount)"></span>
                            <input id="discount" type="number" inputmode="decimal" x-model.number="discountPercent" min="0" :max="maxDiscount"
                                   class="tabular h-9 w-16 rounded-lg border-enamel-200 px-2 text-right text-sm text-ink focus:border-brand-600 focus:ring-2 focus:ring-brand-100"
                                   :class="discountPercent > maxDiscount && '!border-sambal-600 !ring-sambal-100'">
                        </dd>
                    </div>
                    <p x-show="discountPercent > maxDiscount" x-cloak class="text-right text-xs text-sambal-700" x-text="'Maksimal ' + maxDiscount + '%'"></p>
                    <div x-show="taxEnabled" class="flex justify-between text-enamel-500">
                        <dt x-text="'PPN ' + taxPercent + '%'"></dt><dd class="tabular" x-text="formatRupiah(taxAmount)"></dd>
                    </div>
                    <div class="flex items-baseline justify-between border-t border-enamel-200 pt-2.5">
                        <dt class="text-sm font-medium text-ink">Total</dt>
                        <dd class="tabular text-2xl font-bold text-brand-800" x-text="formatRupiah(total)"></dd>
                    </div>
                </dl>

                <div class="grid grid-cols-3 gap-1 rounded-xl bg-enamel-100 p-1" role="group" aria-label="Metode bayar">
                    <template x-for="method in paymentMethods" :key="method.value">
                        <button type="button" @click="paymentMethod = method.value"
                                :aria-pressed="(paymentMethod === method.value).toString()"
                                :class="paymentMethod === method.value ? 'bg-white text-ink shadow-sm ring-1 ring-enamel-200' : 'text-enamel-500 hover:text-ink'"
                                class="min-h-[40px] rounded-lg text-sm font-semibold transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-300"
                                x-text="method.label"></button>
                    </template>
                </div>

                <div x-show="paymentMethod === 'tunai'" class="space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <label for="paid" class="text-sm text-enamel-500">Uang diterima</label>
                        <div class="relative w-44">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-enamel-400">Rp</span>
                            <input id="paid" type="number" inputmode="numeric" x-model.number="paidAmount" placeholder="0"
                                   class="tabular h-11 w-full rounded-xl border-enamel-200 bg-white pl-10 pr-3 text-right text-base font-semibold text-ink focus:border-brand-600 focus:ring-4 focus:ring-brand-100">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="paidAmount = total" :disabled="cart.length === 0" class="min-h-[40px] rounded-lg border border-brand-200 bg-brand-50 px-3 text-sm font-medium text-brand-800 hover:bg-brand-100 disabled:opacity-50">Uang pas</button>
                        <template x-for="cash in cashSuggestions" :key="cash">
                            <button type="button" @click="paidAmount = cash" class="tabular min-h-[40px] rounded-lg border border-enamel-200 bg-white px-3 text-sm text-enamel-700 hover:border-enamel-300 hover:text-ink" x-text="formatRupiah(cash)"></button>
                        </template>
                    </div>
                    <div x-show="changeAmount > 0" class="flex items-baseline justify-between rounded-xl bg-brand-50 px-3 py-2">
                        <span class="text-sm text-brand-800">Kembalian</span>
                        <span class="tabular text-lg font-bold text-brand-800" x-text="formatRupiah(changeAmount)"></span>
                    </div>
                    <p x-show="cart.length > 0 && paidAmount > 0 && paidAmount < total" class="text-sm text-sambal-700">
                        Kurang <span class="tabular" x-text="formatRupiah(total - paidAmount)"></span>
                    </p>
                </div>

                <button type="button" @click="submitTransaction()" :disabled="cart.length === 0 || isSubmitting" class="btn-cta w-full">
                    <span x-show="!isSubmitting">Bayar <span class="tabular" x-text="formatRupiah(total)"></span></span>
                    <span x-show="isSubmitting">Memproses…</span>
                </button>
            </div>
        </aside>

        {{-- Receipt Modal --}}
        <div x-show="showReceipt" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center bg-ink/60 p-0 sm:items-center sm:p-4" @click.self="showReceipt = false" @keydown.escape.window="showReceipt = false">
            <div class="flex max-h-[90dvh] w-full max-w-sm flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" role="dialog" aria-modal="true" aria-labelledby="receipt-title">
                <div class="flex items-center gap-2 border-b border-enamel-200 bg-brand-50 px-5 py-3 text-brand-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p id="receipt-title" class="text-sm font-semibold">Pembayaran lunas</p>
                </div>
                <div id="receipt-content" class="flex-1 overflow-y-auto p-6 text-center" style="font-family: monospace; font-size: 12px;">
                    <h3 class="mb-1 text-lg font-bold text-ink" x-text="storeName"></h3>
                    <p class="mb-4 text-xs text-enamel-500" x-text="storeAddress"></p>
                    <hr class="mb-3 border-dashed">
                    <div class="mb-3 space-y-1 text-left text-xs">
                        <p>No: <span x-text="lastTransaction?.transaction_number"></span></p>
                        <p>Kasir: <span x-text="lastTransaction?.user?.name"></span></p>
                        <p>Tanggal: <span x-text="formatDate(lastTransaction?.created_at)"></span></p>
                    </div>
                    <hr class="mb-3 border-dashed">
                    <template x-for="item in lastTransaction?.items || []" :key="item.id">
                        <div class="mb-2 text-left text-xs">
                            <p class="font-semibold" x-text="item.product_name"></p>
                            <div class="flex justify-between">
                                <span x-text="item.quantity + ' x ' + formatRupiah(item.price)"></span>
                                <span x-text="formatRupiah(item.subtotal)"></span>
                            </div>
                        </div>
                    </template>
                    <hr class="my-3 border-dashed">
                    <div class="space-y-1 text-left text-xs">
                        <div class="flex justify-between"><span>Subtotal</span><span x-text="formatRupiah(lastTransaction?.subtotal)"></span></div>
                        <div x-show="lastTransaction?.discount_amount > 0" class="flex justify-between"><span>Diskon</span><span x-text="'-' + formatRupiah(lastTransaction?.discount_amount)"></span></div>
                        <div x-show="lastTransaction?.tax_amount > 0" class="flex justify-between"><span x-text="'PPN ' + lastTransaction?.tax_percent + '%'"></span><span x-text="formatRupiah(lastTransaction?.tax_amount)"></span></div>
                        <div class="flex justify-between border-t border-dashed pt-1 text-sm font-bold"><span>TOTAL</span><span x-text="formatRupiah(lastTransaction?.total)"></span></div>
                        <div class="flex justify-between"><span x-text="lastTransaction?.payment_method?.toUpperCase()"></span><span x-text="formatRupiah(lastTransaction?.paid_amount)"></span></div>
                        <div x-show="lastTransaction?.change_amount > 0" class="flex justify-between"><span>Kembali</span><span x-text="formatRupiah(lastTransaction?.change_amount)"></span></div>
                    </div>
                    <hr class="my-3 border-dashed">
                    <p class="text-xs text-enamel-500">Terima kasih!</p>
                </div>
                <div class="flex gap-3 border-t border-enamel-200 bg-enamel-50 p-4">
                    <button type="button" @click="printReceipt()" class="btn-secondary flex-1">Cetak struk</button>
                    <button type="button" @click="showReceipt = false" class="btn-primary flex-1">Pesanan baru</button>
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
            paymentMethods: [
                { value: 'tunai', label: 'Tunai' },
                { value: 'qris', label: 'QRIS' },
                { value: 'debit', label: 'Debit' },
            ],
            paymentMethod: 'tunai',
            paidAmount: 0,
            isSubmitting: false,
            showReceipt: false,
            showCart: false,
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

            qtyInCart(productId) { return this.cart.find(i => i.product_id === productId)?.quantity || 0; },
            incrementItem(i) { if (this.cart[i].quantity < this.cart[i].stock) this.cart[i].quantity++; },
            decrementItem(i) { if (this.cart[i].quantity > 1) this.cart[i].quantity--; else this.cart.splice(i, 1); },
            removeItem(i) { this.cart.splice(i, 1); },
            clearCart() { this.cart = []; this.discountPercent = 0; this.paidAmount = 0; },

            get itemCount() { return this.cart.reduce((s, i) => s + i.quantity, 0); },
            get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.quantity, 0); },
            get discountAmount() { return Math.round(this.subtotal * this.discountPercent / 100); },
            get afterDiscount() { return this.subtotal - this.discountAmount; },
            get taxAmount() { return this.taxEnabled ? Math.round(this.afterDiscount * this.taxPercent / 100) : 0; },
            get total() { return this.afterDiscount + this.taxAmount; },
            get changeAmount() { return this.paymentMethod === 'tunai' ? Math.max(0, this.paidAmount - this.total) : 0; },

            // Likely notes a customer hands over: the total rounded up to common denominations.
            get cashSuggestions() {
                if (this.total <= 0) return [];
                const roundUp = step => Math.ceil(this.total / step) * step;
                const options = [roundUp(5000), roundUp(10000), roundUp(50000), roundUp(100000)];
                return [...new Set(options)].filter(v => v > this.total).slice(0, 2);
            },

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
                        this.showCart = false;
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
