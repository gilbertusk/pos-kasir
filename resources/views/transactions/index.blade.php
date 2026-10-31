<x-app-layout>
    @section('title', 'Riwayat Transaksi')
    <div x-data="transactionsPage()">
        {{-- Summary Cards (store-wide figures, owner only) --}}
        @if(Auth::user()->isOwner())
        <p class="mb-3 text-xs font-medium text-slate-400">Ringkasan: <span class="font-bold text-slate-600">{{ $summaryPeriod }}</span></p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center border border-orange-100"><svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Total transaksi</p><p class="text-2xl font-bold text-slate-900 leading-none">{{ $totalTransactions }}</p></div>
            </div>
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100"><svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Total penjualan</p><p class="tabular text-2xl font-bold text-emerald-600 leading-none">{{ formatRupiah($totalRevenue) }}</p></div>
            </div>
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center border border-amber-100"><svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                <div><p class="text-xs font-medium text-slate-400 mb-1">Laba kotor</p><p class="tabular text-2xl font-bold text-amber-600 leading-none">{{ formatRupiah($grossProfit) }}</p></div>
            </div>
        </div>
        @endif

        {{-- Filters --}}
        <div class="card p-4 mb-4">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="input-field w-auto">
                    <span class="text-gray-400">—</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="input-field w-auto">
                </div>
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor transaksi atau nama kasir..." class="input-field pl-10">
                </div>
                <button type="submit" class="btn-primary">Filter</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">No. transaksi</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Tanggal/jam</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Kasir</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Total item</th>
                            <th class="px-4 py-4 text-right font-medium text-slate-500 text-xs">Total</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Metode</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Status</th>
                            <th class="px-4 py-4 text-center font-medium text-slate-500 text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-all duration-200 group">
                            <td class="whitespace-nowrap px-4 py-4 font-mono text-xs font-bold text-slate-900">{{ $trx->transaction_number }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 text-slate-700 font-medium">{{ $trx->user->name ?? '-' }}</td>
                            <td class="px-4 py-4 text-center text-slate-700">{{ $trx->items->sum('quantity') }}</td>
                            <td class="whitespace-nowrap tabular px-4 py-4 text-right font-bold text-slate-900">{{ formatRupiah($trx->total) }}</td>
                            <td class="px-4 py-4 text-center text-xs font-medium text-slate-400">{{ $trx->payment_method === 'qris' ? 'QRIS' : ucfirst($trx->payment_method) }}</td>
                            <td class="px-4 py-4 text-center"><span class="px-3 py-1 text-xs font-bold rounded-full {{ $trx->status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ ucfirst($trx->status) }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="fetchDetail({{ $trx->id }})" class="p-2 rounded-xl hover:bg-primary-50 text-primary-600" title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                    <a href="{{ route('transactions.receipt', [$trx, 'print' => 1]) }}" target="_blank" class="p-2 rounded-xl hover:bg-emerald-50 text-emerald-600" title="Cetak"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $transactions->withQueryString()->links() }}</div>
        </div>

        {{-- Detail Modal --}}
        <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showDetail = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800">Detail Transaksi</h3>
                    <button @click="showDetail = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="p-6 flex-1 overflow-y-auto" x-show="detailData">
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div><p class="text-xs font-medium text-slate-400 mb-1">No. transaksi</p><p class="font-bold text-slate-900" x-text="detailData?.transaction_number"></p></div>
                        <div><p class="text-xs font-medium text-slate-400 mb-1">Tanggal/jam</p><p class="font-bold text-slate-700" x-text="detailData ? new Date(detailData.created_at).toLocaleString('id-ID') : ''"></p></div>
                        <div><p class="text-xs font-medium text-slate-400 mb-1">Kasir</p><p class="font-bold text-slate-700" x-text="detailData?.user?.name"></p></div>
                        <div><p class="text-xs font-medium text-slate-400 mb-1">Status</p><span class="px-3 py-1 text-xs font-semibold rounded-full capitalize" :class="detailData?.status === 'batal' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'" x-text="detailData?.status"></span></div>
                    </div>
                    <div x-show="detailData?.status === 'batal'" class="mb-6 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-sm">
                        <p class="font-bold text-rose-700">Dibatalkan</p>
                        <p class="mt-1 text-rose-700" x-show="detailData?.cancelled_at" x-text="'Oleh ' + (detailData?.canceller?.name || '-') + ', ' + (detailData?.cancelled_at ? new Date(detailData.cancelled_at).toLocaleString('id-ID') : '')"></p>
                        <p class="mt-1 text-rose-700" x-show="detailData?.cancel_reason" x-text="'Alasan: ' + detailData?.cancel_reason"></p>
                    </div>
                    <h4 class="text-xs font-medium text-slate-400 mb-3">Item transaksi</h4>
                    <div class="space-y-3 mb-6">
                        <template x-for="item in detailData?.items || []" :key="item.id">
                            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <div><p class="font-bold text-sm text-slate-800" x-text="item.product_name"></p><p class="tabular text-xs font-medium text-slate-400" x-text="formatRupiah(item.price) + ' x ' + item.quantity"></p></div>
                                <p class="tabular font-bold text-sm text-primary-600" x-text="formatRupiah(item.subtotal)"></p>
                            </div>
                        </template>
                    </div>
                    <div class="space-y-2 text-sm border-t border-slate-100 pt-5">
                        <div class="flex justify-between font-medium text-slate-500"><span>Subtotal</span><span x-text="formatRupiah(detailData?.subtotal || 0)" class="tabular"></span></div>
                        <div x-show="detailData?.discount_amount > 0" class="flex justify-between font-bold text-rose-500"><span>Diskon</span><span x-text="'-' + formatRupiah(detailData?.discount_amount || 0)" class="tabular"></span></div>
                        <div x-show="detailData?.tax_amount > 0" class="flex justify-between font-medium text-slate-500"><span x-text="'PPN ' + detailData?.tax_percent + '%'"></span><span x-text="formatRupiah(detailData?.tax_amount || 0)" class="tabular"></span></div>
                        <div x-show="Number(detailData?.rounding_amount) !== 0" class="flex justify-between font-medium text-slate-500"><span>Pembulatan</span><span x-text="(Number(detailData?.rounding_amount) < 0 ? '-' : '+') + formatRupiah(Math.abs(Number(detailData?.rounding_amount || 0)))" class="tabular"></span></div>
                        <div class="flex justify-between font-bold text-lg text-slate-900 pt-3 border-t border-slate-200 mt-2"><span>TOTAL</span><span class="tabular text-primary-600" x-text="formatRupiah(detailData?.total || 0)"></span></div>
                        <div class="pt-4"><p class="text-xs font-medium text-slate-400 mb-2">Pembayaran</p></div>
                        <div class="flex justify-between font-bold text-slate-600"><span x-text="paymentLabel(detailData?.payment_method)"></span><span x-text="formatRupiah(detailData?.paid_amount || 0)" class="tabular"></span></div>
                        <div x-show="detailData?.change_amount > 0" class="flex justify-between font-bold text-emerald-600"><span class="">Kembali</span><span x-text="formatRupiah(detailData?.change_amount || 0)" class="tabular"></span></div>
                    </div>
                </div>
                @if(Auth::user()->isOwner())
                <form x-show="confirmCancel" x-cloak method="POST" :action="detailData ? '/transactions/' + detailData.id + '/cancel' : '#'" class="space-y-3 border-t border-rose-100 bg-rose-50 p-6">
                    @csrf
                    @method('PATCH')
                    <label for="cancel_reason" class="block text-xs font-medium text-rose-700">Alasan pembatalan *</label>
                    <input id="cancel_reason" type="text" name="cancel_reason" x-ref="cancelReason" required maxlength="255" placeholder="Contoh: salah input pesanan" class="input-field">
                    <p class="text-xs text-rose-700">Stok barang akan dikembalikan. Transaksi yang sudah dibatalkan tidak bisa diaktifkan lagi.</p>
                    <div class="flex gap-3">
                        <button type="button" @click="confirmCancel = false" class="btn-secondary flex-1">Kembali</button>
                        <button type="submit" class="btn-danger flex-1">Ya, batalkan</button>
                    </div>
                </form>
                @endif
                <div x-show="!confirmCancel" class="flex gap-3 p-6 bg-slate-50 border-t border-slate-100">
                    <button @click="showDetail = false" class="btn-secondary flex-1">Tutup</button>
                    @if(Auth::user()->isOwner())
                    <button type="button" x-show="detailData?.status === 'lunas'" @click="confirmCancel = true; $nextTick(() => $refs.cancelReason.focus())" class="btn-secondary flex-1 !text-rose-600">Batalkan transaksi</button>
                    @endif
                    <a :href="detailData ? '/transactions/' + detailData.id + '/receipt?print=1' : '#'" target="_blank" class="btn-primary flex-1 text-center">🖨 Cetak</a>
                </div>
            </div>
        </div>
    </div>

    <script class="tabular">
    function transactionsPage() {
        return {
            showDetail: false,
            detailData: null,
            confirmCancel: false,

            async fetchDetail(id) {
                try {
                    const res = await fetch(`/transactions/${id}`, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    this.detailData = await res.json();
                    this.confirmCancel = false;
                    this.showDetail = true;
                } catch (e) {
                    alert('Gagal memuat detail transaksi. Silakan coba lagi.');
                }
            },

            formatRupiah(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); },
            paymentLabel(m) { return m === 'qris' ? 'QRIS' : (m ? m.charAt(0).toUpperCase() + m.slice(1) : ''); },
        };
    }
    </script>
</x-app-layout>
