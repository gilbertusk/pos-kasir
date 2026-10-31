<x-app-layout>
    @section('title', 'Riwayat Stok')
    <div>
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('products.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-800">&larr; Kembali ke Barang &amp; Stok</a>
        </div>

        {{-- Filters --}}
        <div class="card p-4 mb-4">
            <form method="GET" action="{{ route('products.stockHistory') }}" class="flex flex-wrap items-center gap-3">
                <label for="filter-product" class="sr-only">Barang</label>
                <select id="filter-product" name="product_id" class="input-field w-auto">
                    <option value="">Semua barang</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }} ({{ $product->code }})</option>
                    @endforeach
                </select>
                <label for="filter-type" class="sr-only">Jenis</label>
                <select id="filter-type" name="type" class="input-field w-auto">
                    <option value="">Semua jenis</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="input-field w-auto" aria-label="Dari tanggal">
                    <span class="text-gray-400">—</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="input-field w-auto" aria-label="Sampai tanggal">
                </div>
                <button type="submit" class="btn-primary">Filter</button>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Waktu</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Barang</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Jenis</th>
                            <th class="px-4 py-4 text-right font-medium text-slate-500 text-xs">Perubahan</th>
                            <th class="px-4 py-4 text-right font-medium text-slate-500 text-xs">Stok</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Keterangan</th>
                            <th class="px-4 py-4 text-left font-medium text-slate-500 text-xs">Oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($movements as $movement)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 font-bold text-slate-800">{{ $movement->product->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-full {{ match($movement->type) {
                                    'masuk', 'awal' => 'bg-brand-100 text-brand-800',
                                    'penyesuaian' => 'bg-kunyit-100 text-kunyit-800',
                                    'batal' => 'bg-enamel-100 text-enamel-700',
                                    default => 'bg-slate-100 text-slate-600',
                                } }}">{{ $movement->type_label }}</span>
                            </td>
                            <td class="whitespace-nowrap tabular px-4 py-3 text-right font-bold {{ $movement->quantity > 0 ? 'text-brand-700' : 'text-rose-600' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="whitespace-nowrap tabular px-4 py-3 text-right text-slate-500">{{ $movement->stock_before }} &rarr; <span class="font-bold text-slate-800">{{ $movement->stock_after }}</span></td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($movement->transaction)
                                    <span class="font-mono text-xs">{{ $movement->transaction->transaction_number }}</span>
                                @endif
                                @if($movement->note)
                                    <span class="block">{{ $movement->note }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ $movement->user->name ?? 'Sistem' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada riwayat stok</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100">{{ $movements->withQueryString()->links() }}</div>
        </div>
    </div>
</x-app-layout>
