<x-app-layout>
    @section('title', 'Pengeluaran')
    <div x-data="{ showAddModal: false, showEditModal: false, editExpense: {} }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div></div>
            <div class="flex gap-3">
                <button @click="window.print()" class="btn-secondary !rounded-xl !py-3 shadow-sm"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>Cetak Pengeluaran</button>
                <button @click="showAddModal = true" class="btn-primary !rounded-xl !py-3 shadow-primary-200"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Tambah Pengeluaran</button>
            </div>
        </div>

        {{-- Summary --}}
        <div class="card p-6 mb-8 flex items-center justify-between bg-slate-50/50 border-slate-100 shadow-sm rounded-3xl">
            <div class="flex items-center gap-5">
                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center border border-rose-100 shadow-sm"><svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg></div>
                <div><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pengeluaran</p><p class="text-3xl font-black text-rose-600 leading-none">{{ formatRupiah($totalExpenses) }}</p></div>
            </div>
            <span class="px-4 py-2 bg-white rounded-xl border border-slate-200 text-xs font-bold text-slate-500 shadow-sm">{{ $expenseCount }} Catatan</span>
        </div>

        {{-- Filters --}}
        <div class="card p-4 mb-4">
            <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="category" class="input-field w-auto" onchange="this.form.submit()">
                    <option value="semua" {{ request('category') == 'semua' || !request('category') ? 'selected' : '' }}>Semua kategori</option>
                    @foreach(['listrik','gaji','perlengkapan','sewa'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="input-field w-auto">
                    <span class="text-gray-400">—</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="input-field w-auto">
                </div>
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi pengeluaran..." class="input-field pl-10">
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
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Tanggal</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Kategori</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Deskripsi</th>
                            <th class="px-4 py-4 text-right font-bold text-slate-500 uppercase text-[10px] tracking-wider">Nominal</th>
                            <th class="px-4 py-4 text-left font-bold text-slate-500 uppercase text-[10px] tracking-wider">Admin</th>
                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase text-[10px] tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50 transition-all duration-200 group">
                            <td class="px-4 py-4 text-slate-500 font-medium">{{ $expense->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-4"><span class="px-3 py-1 text-[10px] font-black uppercase rounded-full {{ \App\Models\Expense::getCategoryBadgeColor($expense->category) }}">{{ $expense->category }}</span></td>
                            <td class="px-4 py-4 text-slate-800 font-bold">{{ $expense->description ?? '-' }}</td>
                            <td class="px-4 py-4 text-right font-black text-rose-600">{{ formatRupiah($expense->amount) }}</td>
                            <td class="px-4 py-4 text-slate-400 font-bold text-xs uppercase">{{ $expense->user->name ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="editExpense = {{ $expense->toJson() }}; showEditModal = true" class="p-2 rounded-xl hover:bg-primary-50 text-primary-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">@csrf @method('DELETE')
                                        <button class="p-2 rounded-xl hover:bg-rose-50 text-rose-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada pengeluaran</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $expenses->withQueryString()->links() }}</div>
        </div>

        {{-- Add Modal --}}
        <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showAddModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div><h3 class="text-lg font-black text-slate-800">Tambah Pengeluaran</h3><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Catat operasional toko</p></div>
                    <button @click="showAddModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal *</label><input type="date" name="date" value="{{ date('Y-m-d') }}" required class="input-field font-bold"></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori *</label><select name="category" required class="input-field font-bold"><option value="">Pilih kategori</option><option value="listrik">Listrik</option><option value="gaji">Gaji</option><option value="perlengkapan">Perlengkapan</option><option value="sewa">Sewa</option></select></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Deskripsi</label><textarea name="description" rows="2" class="input-field font-bold" placeholder="Contoh: Pembayaran listrik Mei"></textarea></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nominal (Rp) *</label><input type="number" name="amount" min="0" required class="input-field !text-lg font-black bg-slate-50" placeholder="0"></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lampiran (Bukti)</label><input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="input-field"></div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showAddModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showEditModal = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <h3 class="text-lg font-black text-slate-800">Edit Pengeluaran</h3>
                    <button @click="showEditModal = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form :action="'/expenses/' + editExpense.id" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tanggal *</label><input type="date" name="date" :value="editExpense.date?.split('T')[0]" required class="input-field font-bold"></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Kategori *</label><select name="category" required class="input-field font-bold"><option value="listrik" :selected="editExpense.category === 'listrik'">Listrik</option><option value="gaji" :selected="editExpense.category === 'gaji'">Gaji</option><option value="perlengkapan" :selected="editExpense.category === 'perlengkapan'">Perlengkapan</option><option value="sewa" :selected="editExpense.category === 'sewa'">Sewa</option></select></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Deskripsi</label><textarea name="description" rows="2" class="input-field font-bold" x-text="editExpense.description"></textarea></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nominal (Rp) *</label><input type="number" name="amount" :value="editExpense.amount" min="0" required class="input-field !text-lg font-black bg-slate-50"></div>
                    <div><label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lampiran</label><input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf" class="input-field"></div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showEditModal = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
