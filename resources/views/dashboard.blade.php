<x-app-layout>
    @section('title', 'Dashboard')

    <div class="space-y-8">
        {{-- Welcome Header --}}
        <div class="card p-8 bg-gradient-to-br from-indigo-600 to-primary-800 border-none relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-3xl font-black text-white mb-2">Selamat Datang, {{ Auth::user()->name }}! 👋</h2>
                <p class="text-indigo-100 font-medium opacity-90 max-w-lg">Sistem POS Anda sudah siap untuk mencatat transaksi hari ini. Pantau performa bisnis Anda secara real-time dari sini.</p>
            </div>
            {{-- Abstract Shapes --}}
            <div class="absolute -right-10 -top-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute right-20 -bottom-20 w-48 h-48 bg-indigo-400 opacity-20 rounded-full blur-2xl"></div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="card p-6 flex items-center gap-5 group hover:shadow-xl hover:shadow-indigo-50 transition-all duration-300">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center border border-indigo-100 text-indigo-600 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Mulai Transaksi</p>
                    <a href="{{ route('kasir.index') }}" class="text-lg font-black text-slate-800 hover:text-indigo-600 transition-colors">Buka Kasir Baru →</a>
                </div>
            </div>

            <div class="card p-6 flex items-center gap-5 group hover:shadow-xl hover:shadow-emerald-50 transition-all duration-300">
                <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center border border-emerald-100 text-emerald-600 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Performa Bisnis</p>
                    <a href="{{ route('reports.index') }}" class="text-lg font-black text-slate-800 hover:text-emerald-600 transition-colors">Lihat Laporan →</a>
                </div>
            </div>

            <div class="card p-6 flex items-center gap-5 group hover:shadow-xl hover:shadow-rose-50 transition-all duration-300">
                <div class="w-14 h-14 bg-rose-50 rounded-2xl flex items-center justify-center border border-rose-100 text-rose-600 group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Inventaris Barang</p>
                    <a href="{{ route('products.index') }}" class="text-lg font-black text-slate-800 hover:text-rose-600 transition-colors">Cek Stok Barang →</a>
                </div>
            </div>
        </div>

        {{-- Tips Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="card p-8">
                <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">Tips Penggunaan POS</h3>
                <div class="space-y-4">
                    <div class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-2xl">💡</span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Gunakan Barcode Scanner</p>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Hubungkan scanner Anda ke USB dan arahkan ke kolom pencarian produk untuk input cepat.</p>
                        </div>
                    </div>
                    <div class="flex gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-2xl">⚡</span>
                        <div>
                            <p class="font-bold text-slate-800 text-sm">Shortcut Kasir</p>
                            <p class="text-xs text-slate-500 font-medium leading-relaxed">Tekan Enter pada kolom nominal pembayaran untuk mempercepat proses penyelesaian transaksi.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card p-8 bg-slate-900 border-none relative overflow-hidden flex flex-col justify-center">
                <div class="relative z-10">
                    <span class="px-3 py-1 bg-indigo-500 text-white text-[10px] font-black uppercase tracking-widest rounded-lg mb-4 inline-block">Update Pro</span>
                    <h3 class="text-xl font-black text-white mb-2 leading-tight">Butuh Fitur Kustom?</h3>
                    <p class="text-slate-400 text-sm font-medium mb-6">Kami siap membantu menyesuaikan sistem ini dengan kebutuhan spesifik restoran atau toko Anda.</p>
                    <a href="#" class="text-indigo-400 font-black text-xs uppercase tracking-widest hover:text-indigo-300">Hubungi Tim Support →</a>
                </div>
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-indigo-600/10 rounded-full blur-3xl"></div>
            </div>
        </div>
    </div>
</x-app-layout>

