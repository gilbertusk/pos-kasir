<x-app-layout>
    @section('title', 'Pengaturan')
    <div x-data="{ activeTab: 'profil', showAddUser: false }">
        {{-- Tabs --}}
        <div class="flex gap-1 mb-8 bg-white rounded-xl border border-slate-200 p-1 w-fit shadow-sm">
            @foreach(['profil' => '🏪 Profil Toko', 'pajak' => '🧾 Struk & Pajak', 'pengguna' => '👥 Pengguna', 'data' => '💾 Data'] as $key => $label)
                <button @click="activeTab = '{{ $key }}'" :class="activeTab === '{{ $key }}' ? 'bg-primary-600 text-white shadow-md shadow-orange-300/40' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2.5 text-sm font-bold rounded-lg transition-all duration-200">{{ $label }}</button>
            @endforeach
        </div>

        {{-- Profil Toko --}}
        <div x-show="activeTab === 'profil'" class="card p-8">
            <h3 class="text-sm font-bold text-slate-400 mb-1">Informasi toko</h3>
            <p class="text-xs text-slate-400 mb-8">Data identitas dan kontak toko Anda yang akan tampil di struk</p>
            <form method="POST" action="{{ route('settings.updateStore') }}" enctype="multipart/form-data" class="space-y-6 max-w-xl">
                @csrf
                <div><label class="block text-xs font-medium text-slate-400 mb-1">Nama toko *</label><input type="text" name="store_name" value="{{ $settings['store_name'] ?? '' }}" required class="input-field font-bold"></div>
                <div><label class="block text-xs font-medium text-slate-400 mb-1">Alamat lengkap *</label><textarea name="store_address" rows="2" required class="input-field font-bold">{{ $settings['store_address'] ?? '' }}</textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Nomor telepon *</label><input type="text" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" required class="input-field font-bold"></div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Nomor WhatsApp</label><input type="text" name="store_whatsapp" value="{{ $settings['store_whatsapp'] ?? '' }}" class="input-field font-bold"></div>
                </div>
                <div><label class="block text-xs font-medium text-slate-400 mb-1">NPWP (opsional)</label><input type="text" name="store_npwp" value="{{ $settings['store_npwp'] ?? '' }}" class="input-field font-bold"></div>
                <div><label class="block text-xs font-medium text-slate-400 mb-1">Logo toko</label><input type="file" name="store_logo" accept="image/*" class="input-field"></div>
                <button type="submit" class="btn-primary !py-3 px-8 shadow-primary-200">Simpan Perubahan</button>
            </form>
        </div>

        {{-- Struk & Pajak --}}
        <div x-show="activeTab === 'pajak'" class="space-y-8">
            <form method="POST" action="{{ route('settings.updateTax') }}" class="card p-8 space-y-8">
                @csrf
                <div>
                    <h3 class="text-sm font-bold text-slate-400 mb-6">Pajak dan diskon</h3>
                    <div class="space-y-4 max-w-xl">
                        <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <div><p class="font-bold text-slate-800">PPN 11%</p><p class="text-xs text-slate-400">Aktifkan PPN otomatis pada setiap transaksi</p></div>
                            <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="tax_enabled" value="1" {{ ($settings['tax_enabled'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer"><div class="w-12 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div></label>
                        </div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Diskon maksimal (%)</label><input type="number" name="max_discount" value="{{ $settings['max_discount'] ?? '50' }}" min="0" max="100" class="input-field w-40 font-bold"><p class="text-xs text-slate-400 mt-2 font-medium tracking-tight italic">Batas maksimal diskon yang bisa diberikan kasir</p></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Pembulatan harga</label><select name="price_rounding" class="input-field w-60 font-bold"><option value="0" {{ ($settings['price_rounding'] ?? '0') == '0' ? 'selected' : '' }}>Tidak ada pembulatan</option><option value="100" {{ ($settings['price_rounding'] ?? '0') == '100' ? 'selected' : '' }}>Rp 100</option><option value="500" {{ ($settings['price_rounding'] ?? '0') == '500' ? 'selected' : '' }}>Rp 500</option><option value="1000" {{ ($settings['price_rounding'] ?? '0') == '1000' ? 'selected' : '' }}>Rp 1.000</option></select></div>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-400 mb-6">Format dan printer</h3>
                    <div class="space-y-6 max-w-xl">
                        @php
                            $numberFormat = old('transaction_format', $settings['transaction_format'] ?? \App\Support\TransactionNumberFormat::DEFAULT_FORMAT);
                            // Show the period actually in use, so an older saved combination that the format can not support does not block saving.
                            $resetNumber = old('reset_number', \App\Support\TransactionNumberFormat::effectivePeriod(
                                \App\Support\TransactionNumberFormat::isValid($numberFormat) ? $numberFormat : \App\Support\TransactionNumberFormat::DEFAULT_FORMAT,
                                $settings['reset_number'] ?? \App\Support\TransactionNumberFormat::DEFAULT_RESET
                            ));
                        @endphp
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Format nomor transaksi</label><input type="text" name="transaction_format" value="{{ $numberFormat }}" maxlength="50" required class="input-field font-bold"><p class="text-xs text-slate-400 mt-2 font-medium tracking-tight italic">{DD} = Tanggal, {MM} = Bulan, {YYYY} = Tahun, {YY} = Tahun 2 digit, {XXX} = Nomor urut (jumlah X = jumlah digit)</p></div>
                        <div><label class="block text-xs font-medium text-slate-400 mb-1">Reset nomor urut</label><select name="reset_number" class="input-field w-60 font-bold">@foreach(['daily' => 'Reset setiap hari', 'monthly' => 'Reset setiap bulan', 'yearly' => 'Reset setiap tahun', 'never' => 'Tidak pernah reset'] as $value => $label)<option value="{{ $value }}" {{ $resetNumber === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select><p class="text-xs text-slate-400 mt-2 font-medium tracking-tight italic">Reset harian butuh {DD} {MM} {YYYY}, bulanan butuh {MM} {YYYY}, tahunan butuh {YYYY} di format.</p></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs font-medium text-slate-400 mb-1">Ukuran kertas</label><select name="printer_size" class="input-field font-bold"><option value="58mm" {{ ($settings['printer_size'] ?? '80mm') == '58mm' ? 'selected' : '' }}>Thermal 58mm</option><option value="80mm" {{ ($settings['printer_size'] ?? '80mm') == '80mm' ? 'selected' : '' }}>Thermal 80mm</option></select></div>
                            <div><label class="block text-xs font-medium text-slate-400 mb-1">Margin (mm)</label><input type="number" name="printer_margin" value="{{ $settings['printer_margin'] ?? '5' }}" min="0" class="input-field font-bold"></div>
                        </div>
                        <div class="flex items-center justify-between p-5 bg-slate-50 rounded-2xl border border-slate-100">
                            <div><p class="font-bold text-slate-800">Cetak Otomatis</p><p class="text-xs text-slate-400">Cetak struk secara otomatis setelah pembayaran</p></div>
                            <label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" name="auto_print" value="1" {{ ($settings['auto_print'] ?? '1') == '1' ? 'checked' : '' }} class="sr-only peer"><div class="w-12 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div></label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary">Simpan Pengaturan</button>
            </form>
        </div>

        <div x-show="activeTab === 'pengguna'" class="card overflow-hidden">
            <div class="flex items-center justify-between p-8 border-b border-slate-100 bg-slate-50/30">
                <h3 class="text-sm font-bold text-slate-400">Pengguna</h3>
                <button @click="showAddUser = true" class="btn-primary !rounded-xl text-xs !py-2 px-4 shadow-primary-200 font-bold flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>Baru</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium text-slate-500 text-xs">Nama dan email</th>
                            <th class="px-6 py-4 text-center font-medium text-slate-500 text-xs">Role</th>
                            <th class="px-6 py-4 text-center font-medium text-slate-500 text-xs">Status</th>
                            <th class="px-6 py-4 text-center font-medium text-slate-500 text-xs">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $user)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs font-medium text-slate-400">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-center"><span class="px-3 py-1 text-xs font-bold rounded-full {{ $user->role === 'owner' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700' }}">{{ $user->role === 'owner' ? 'Owner' : 'Kasir' }}</span></td>
                            <td class="px-6 py-4 text-center"><span class="px-3 py-1 text-xs font-bold rounded-full {{ $user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <form method="POST" action="{{ route('settings.toggleUser', $user) }}">@csrf @method('PATCH')<button class="p-2 rounded-xl hover:bg-slate-100 text-slate-600 transition-colors" title="Toggle Status"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></button></form>
                                    <form method="POST" action="{{ route('settings.destroyUser', $user) }}" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button class="p-2 rounded-xl hover:bg-rose-50 text-rose-600 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Data --}}
        <div x-show="activeTab === 'data'" class="card p-8">
            <h3 class="text-sm font-bold text-slate-400 mb-6">Data dan backup</h3>
            <div class="space-y-4 max-w-xl">
                <div class="p-6 bg-amber-50 border border-amber-100 rounded-2xl">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm text-amber-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg></div>
                        <p class="text-sm font-bold text-amber-700 tracking-tight">Keamanan data</p>
                    </div>
                    <p class="text-xs text-amber-600 leading-relaxed font-bold">Pastikan melakukan backup database secara berkala melalui phpMyAdmin atau server manajemen Anda. Data transaksi dan produk adalah aset paling berharga Anda.</p>
                </div>
            </div>
        </div>

        {{-- Add User Modal --}}
        <div x-show="showAddUser" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" @click.self="showAddUser = false">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden flex flex-col">
                <div class="flex items-center justify-between p-6 border-b border-slate-100">
                    <div><h3 class="text-lg font-bold text-slate-800">Tambah Pengguna</h3><p class="text-xs font-medium text-slate-400">Berikan akses ke sistem POS</p></div>
                    <button @click="showAddUser = false" class="p-2 rounded-xl hover:bg-slate-100 transition-colors"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <form method="POST" action="{{ route('settings.storeUser') }}" class="p-6 space-y-4">
                    @csrf
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Nama lengkap *</label><input type="text" name="name" required class="input-field font-bold"></div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Email *</label><input type="email" name="email" required class="input-field font-bold"></div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Password *</label><input type="password" name="password" required minlength="6" class="input-field font-bold"></div>
                    <div><label class="block text-xs font-medium text-slate-400 mb-1">Role akses *</label><select name="role" required class="input-field font-bold"><option value="kasir">Kasir (Hanya POS)</option><option value="owner">Owner (Semua Fitur)</option></select></div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showAddUser = false" class="btn-secondary flex-1">Batal</button>
                        <button type="submit" class="btn-primary flex-1">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
