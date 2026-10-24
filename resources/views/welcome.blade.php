<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - Sistem kasir modern</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
            .hero-gradient {
                background: radial-gradient(circle at 0% 0%, rgba(249, 115, 22, 0.05) 0%, transparent 50%),
                            radial-gradient(circle at 100% 100%, rgba(220, 38, 38, 0.05) 0%, transparent 50%);
            }
            .floating { animation: float 6s ease-in-out infinite; }
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }
        </style>
    </head>
    <body class="antialiased text-slate-800 bg-enamel-50 selection:bg-primary-100 selection:text-primary-900 overflow-x-hidden">
        {{-- Navbar --}}
        <nav class="fixed top-0 left-0 w-full z-50 glass border-b border-orange-100 h-20">
            <div class="max-w-7xl mx-auto px-6 h-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-800 rounded-xl flex items-center justify-center shadow-lg shadow-orange-300/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-xl font-black tracking-tight">G <span class="text-primary-600">Pos</span></span>
                </div>
                <div class="flex items-center gap-6">
                    @auth
                        <a href="{{ route('kasir.index') }}" class="btn-primary !py-2.5 px-6">Buka Kasir</a>
                    @else
                        <a href="{{ route('login') }}" class="btn-primary !py-2.5 px-6">Masuk</a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="relative pt-20">
            {{-- Hero Section --}}
            <section class="relative py-24 lg:py-32 overflow-hidden hero-gradient">
                <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center">
                    <div class="relative z-10">
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-orange-50 border border-orange-100 text-primary-600 text-xs font-black uppercase tracking-widest mb-6">
                            🚀 Generasi Baru POS System
                        </span>
                        <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] mb-8">
                            Kelola Bisnis Lebih <span class="text-primary-600">Cerdas</span> & Cepat.
                        </h1>
                        <p class="text-lg text-slate-500 font-medium leading-relaxed mb-10 max-w-lg">
                            Sistem kasir modern yang dirancang khusus untuk mempercepat transaksi, mengelola stok secara real-time, dan memberikan laporan bisnis yang akurat.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 bg-brand-800 hover:bg-brand-900 text-white font-extrabold text-sm rounded-xl transition-all duration-300 shadow-xl shadow-orange-500/20">Masuk Sekarang →</a>
                            <a href="#features" class="btn-secondary !py-4 px-8 text-sm">Pelajari Fitur</a>
                        </div>
                    </div>
                    <div class="relative lg:h-[600px] flex items-center justify-center">
                        {{-- Abstract Visual --}}
                        <div class="absolute inset-0 bg-primary-600/5 rounded-full blur-3xl animate-pulse"></div>
                        <div class="relative z-10 w-full max-w-md floating">
                            <div class="card p-4 !rounded-[2.5rem] shadow-2xl rotate-3 bg-white/80 backdrop-blur">
                                <div class="bg-slate-50 rounded-[2rem] p-6 border border-slate-100">
                                    <div class="flex justify-between items-center mb-8">
                                        <div class="space-y-1">
                                            <div class="w-24 h-3 bg-slate-200 rounded-full"></div>
                                            <div class="w-16 h-3 bg-slate-100 rounded-full"></div>
                                        </div>
                                        <div class="w-10 h-10 bg-orange-100 rounded-xl"></div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center p-3 bg-white rounded-2xl border border-slate-50 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-orange-50 rounded-lg"></div>
                                                <div class="w-20 h-2 bg-slate-100 rounded-full"></div>
                                            </div>
                                            <div class="w-12 h-2 bg-primary-500 rounded-full"></div>
                                        </div>
                                        <div class="flex justify-between items-center p-3 bg-white rounded-2xl border border-slate-50 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-emerald-50 rounded-lg"></div>
                                                <div class="w-24 h-2 bg-slate-100 rounded-full"></div>
                                            </div>
                                            <div class="w-10 h-2 bg-emerald-500 rounded-full"></div>
                                        </div>
                                    </div>
                                    <div class="mt-8 pt-6 border-t border-slate-100">
                                        <div class="flex justify-between items-center">
                                            <div class="w-20 h-4 bg-slate-800 rounded-full"></div>
                                            <div class="w-16 h-6 bg-primary-600 rounded-full"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -left-12 bottom-12 card p-4 !rounded-3xl shadow-xl -rotate-6 bg-white/90 backdrop-blur w-48">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Penjualan Hari Ini</p>
                                <p class="text-2xl font-black text-slate-900 mb-1">Rp 4.2M</p>
                                <div class="flex items-center gap-1 text-emerald-500 text-[10px] font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                    12.5% vs kemarin
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Features Section --}}
            <section id="features" class="py-24 bg-white">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="text-center max-w-2xl mx-auto mb-20">
                        <h2 class="text-3xl lg:text-4xl font-black text-slate-900 mb-6">Fitur Unggulan Untuk Bisnis Anda</h2>
                        <p class="text-slate-500 font-medium">Semua yang Anda butuhkan untuk menjalankan operasional bisnis dalam satu platform yang terintegrasi.</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8">
                        <div class="card p-8 group hover:border-orange-200 transition-all">
                            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-primary-600 mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Kasir Super Cepat</h3>
                            <p class="text-slate-500 text-sm leading-relaxed font-medium">Proses transaksi hanya dalam hitungan detik dengan antarmuka yang intuitif dan responsif.</p>
                        </div>
                        <div class="card p-8 group hover:border-emerald-200 transition-all">
                            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Manajemen Stok</h3>
                            <p class="text-slate-500 text-sm leading-relaxed font-medium">Pantau inventaris barang Anda secara real-time dan dapatkan notifikasi saat stok menipis.</p>
                        </div>
                        <div class="card p-8 group hover:border-orange-200 transition-all">
                            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 mb-6 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-4">Laporan Akurat</h3>
                            <p class="text-slate-500 text-sm leading-relaxed font-medium">Analisis performa bisnis Anda dengan laporan penjualan, laba rugi, dan tren harian yang mendalam.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="py-12 bg-slate-50 border-t border-slate-100">
                <div class="max-w-7xl mx-auto px-6 text-center">
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">&copy; {{ date('Y') }} {{ config('app.name') }}. Built for speed and elegance.</p>
                </div>
            </footer>
        </main>
    </body>
</html>
