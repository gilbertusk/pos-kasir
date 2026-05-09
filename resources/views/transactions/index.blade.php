<x-app-layout>
    @section('title', 'Riwayat Transaksi')
    <div x-data="{ showDetail: false, detailData: null }">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center border border-indigo-100"><svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg></div>
                <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Transaksi</p><p class="text-3xl font-black text-slate-900 leading-none">{{ $totalTransactions }}</p></div>
            </div>
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100"><svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Penjualan</p><p class="text-3xl font-black text-emerald-600 leading-none">{{ formatRupiah($totalRevenue) }}</p></div>
            </div>
            <div class="card p-6 flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center border border-amber-100"><svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
                <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Laba Kotor</p><p class="text-3xl font-black text-amber-600 leading-none">{{ formatRupiah($grossProfit) }}</p></div>
            </div>
        </div>

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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode barang..." class="input-field pl-10">
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
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">No. Transaksi</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Tanggal/Jam</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Kasir</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Total Item</th>
                            <th class="px-4 py-4 text-right font-bold text-slate-500 uppercase text-[10px] tracking-wider">Total</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Metode</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Status</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-all duration-200 group">
                            <td class="px-4 py-4 font-mono text-xs font-bold text-slate-900">{{ $trx->transaction_number }}</td>
                            <td class="px-4 py-4 text-slate-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 text-slate-700 font-medium">{{ $trx->user->name ?? '-' }}</td>
                            <td class="px-4 py-4 text-center text-slate-700">{{ $trx->items->sum('quantity') }}</td>
                            <td class="px-4 py-4 text-right font-black text-slate-900">{{ formatRupiah($trx->total) }}</td>
                            <td class="px-4 py-4 text-center uppercase text-[10px] font-black text-slate-400">{{ $trx->payment_method }}</td>
                            <td class="px-4 py-4 text-center"><span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ $trx->status === 'lunas' ? 'bg-indigo-100 text-indigo-700' : 'bg-rose-100 text-rose-700' }}">{{ $trx->status }}</span></td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="fetchDetail({{ $trx->id }})" class="p-2 rounded-xl hover:bg-primary-50 text-primary-600" title="Detail"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></button>
                                    <a href="{{ route('transactions.receipt', $trx) }}" target="_blank" class="p-2 rounded-xl hover:bg-emerald-50 text-emerald-600" title="Cetak"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
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
                    <h3 class="text-lg font-black text-slate-800">Detail Transaksi</h3>
                    <button @click="showDetail = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="p-6 flex-1 overflow-y-auto" x-show="detailData">
                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                        <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">No. Transaksi</p><p class="font-black text-slate-900" x-text="detailData?.transaction_number"></p></div>
                        <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanggal/Jam</p><p class="font-bold text-slate-700" x-text="detailData ? new Date(detailData.created_at).toLocaleString('id-ID') : ''"></p></div>
                        <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kasir</p><p class="font-bold text-slate-700" x-text="detailData?.user?.name"></p></div>
                        <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</p><span class="px-3 py-1 text-[10px] font-black uppercase rounded-full bg-indigo-100 text-indigo-700" x-text="detailData?.status"></span></div>
                    </div>
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Item Transaksi</h4>
                    <div class="space-y-3 mb-6">
                        <template x-for="item in detailData?.items || []" :key="item.id">
                            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <div><p class="font-bold text-sm text-slate-800" x-text="item.product_name"></p><p class="text-[10px] font-bold text-slate-400" x-text="formatRupiah(item.price) + ' x ' + item.quantity"></p></div>
                                <p class="font-black text-sm text-primary-600" x-text="formatRupiah(item.subtotal)"></p>
                            </div>
                        </template>
                    </div>
                    <div class="space-y-2 text-sm border-t border-slate-100 pt-5">
                        <div class="flex justify-between font-medium text-slate-500"><span>Subtotal</span><span x-text="formatRupiah(detailData?.subtotal || 0)"></span></div>
                        <div x-show="detailData?.discount_amount > 0" class="flex justify-between font-bold text-rose-500"><span>Diskon</span><span x-text="'-' + formatRupiah(detailData?.discount_amount || 0)"></span></div>
                        <div x-show="detailData?.tax_amount > 0" class="flex justify-between font-medium text-slate-500"><span x-text="'PPN ' + detailData?.tax_percent + '%'"></span><span x-text="formatRupiah(detailData?.tax_amount || 0)"></span></div>
                        <div class="flex justify-between font-black text-lg text-slate-900 pt-3 border-t border-slate-200 mt-2"><span>TOTAL</span><span class="text-primary-600" x-text="formatRupiah(detailData?.total || 0)"></span></div>
                        <div class="pt-4"><p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Pembayaran</p></div>
                        <div class="flex justify-between font-bold text-slate-600"><span x-text="detailData?.payment_method?.toUpperCase()"></span><span x-text="formatRupiah(detailData?.paid_amount || 0)"></span></div>
                        <div x-show="detailData?.change_amount > 0" class="flex justify-between font-black text-emerald-600"><span class="uppercase tracking-widest">Kembali</span><span x-text="formatRupiah(detailData?.change_amount || 0)"></span></div>
                    </div>
                </div>
                <div class="flex gap-3 p-6 bg-slate-50 border-t border-slate-100">
                    <button @click="showDetail = false" class="btn-secondary flex-1">Tutup</button>
                    <a :href="detailData ? '/transactions/' + detailData.id + '/receipt' : '#'" target="_blank" class="btn-primary flex-1 text-center">🖨 Cetak</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function fetchDetail(id) {
        fetch(`/transactions/${id}`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                Alpine.store('detail', data);
                document.querySelector('[x-data]').__x.$data.detailData = data;
                document.querySelector('[x-data]').__x.$data.showDetail = true;
            });
    }
    </script>
</x-app-layout>
