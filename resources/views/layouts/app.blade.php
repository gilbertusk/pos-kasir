<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: true }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System') - Pos System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen text-slate-900">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="fixed top-0 left-0 h-full bg-slate-900 transition-all duration-500 z-40 flex flex-col text-white shadow-[20px_0_60px_-15px_rgba(0,0,0,0.3)]">
            {{-- Logo --}}
            <div class="flex items-center justify-between p-6 border-b border-slate-800/50">
                <div class="flex items-center space-x-3 overflow-hidden" x-show="sidebarOpen" x-transition>
                    <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-900/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-base font-black text-white tracking-tight truncate">Indigo <span class="text-primary-400">POS</span></h1>
                        <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest truncate">Professional</p>
                    </div>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-xl hover:bg-slate-800 text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="!sidebarOpen && 'rotate-180'"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'kasir.index', 'label' => 'Point of Sale', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
                        ['route' => 'products.index', 'ownerOnly' => true, 'label' => 'Inventaris', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                        ['route' => 'transactions.index', 'label' => 'Transaksi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                        ['route' => 'reports.index', 'ownerOnly' => true, 'label' => 'Analitik', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                        ['route' => 'expenses.index', 'ownerOnly' => true, 'label' => 'Pengeluaran', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                        ['route' => 'settings.index', 'ownerOnly' => true, 'label' => 'Pengaturan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @continue(($item['ownerOnly'] ?? false) && ! Auth::user()->isOwner())
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center px-4 py-3.5 rounded-2xl transition-all duration-300 group relative
                              {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route']))
                                 ? 'bg-primary-600 text-white shadow-2xl shadow-primary-900/40'
                                 : 'text-slate-500 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-bold tracking-wide">{{ $item['label'] }}</span>
                        @if(request()->routeIs($item['route']))
                            <div class="absolute right-2 w-1.5 h-1.5 rounded-full bg-white animate-pulse"></div>
                        @endif
                    </a>
                @endforeach
            </nav>

            {{-- User Profile Mini --}}
            <div class="p-4 border-t border-slate-800/50">
                <a href="{{ route('profile.edit') }}" class="flex items-center p-3 rounded-2xl hover:bg-slate-800 transition-colors group">
                    <div class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center border border-slate-700 group-hover:border-primary-500 transition-colors">
                        <span class="text-sm font-black text-primary-400">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                    <div x-show="sidebarOpen" x-transition class="ml-3 min-w-0">
                        <p class="text-xs font-black text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-500 font-bold uppercase truncate">Pengaturan Profil</p>
                    </div>
                </a>
            </div>

            {{-- Logout --}}
            <div class="p-4 pt-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-3 rounded-2xl text-slate-500 hover:bg-rose-500/10 hover:text-rose-400 transition-all duration-300 group">
                        <svg class="w-5 h-5 flex-shrink-0 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-bold">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-500 min-h-screen flex flex-col">
            {{-- Header --}}
            <header class="sticky top-0 z-30 bg-slate-50/80 backdrop-blur-xl border-b border-slate-200/60 h-20 flex items-center">
                <div class="flex items-center justify-between px-8 w-full">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">@yield('title', 'Dashboard')</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">@yield('subtitle', 'Indigo POS System')</p>
                    </div>
                    <div class="flex items-center space-x-6">
                        {{-- Search Bar Mini --}}
                        <div class="hidden lg:flex items-center bg-slate-200/50 rounded-xl px-4 py-2 border border-slate-200">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" placeholder="Cari..." class="bg-transparent border-none focus:ring-0 text-sm font-bold text-slate-700 placeholder:text-slate-400 w-40">
                        </div>

                        {{-- Notifications --}}
                        <button class="relative p-2.5 rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-primary-600 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @php $lowStock = \App\Models\Product::lowStock()->count(); @endphp
                            @if($lowStock > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-slate-50 animate-bounce">{{ $lowStock }}</span>
                            @endif
                        </button>


                        {{-- Clock --}}
                        <div class="hidden md:flex items-center space-x-2 px-3 py-1.5 bg-slate-100 rounded-lg">
                            <span class="text-xs font-medium text-slate-500">Waktu</span>
                            <span x-data="{ time: '' }"
                                  x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB' }, 1000)"
                                  x-text="time"
                                  class="text-sm font-semibold text-slate-700"></span>
                        </div>

                        {{-- User Info --}}
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 bg-primary-100 rounded-full flex items-center justify-center border border-primary-200">
                                <span class="text-sm font-bold text-primary-700">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                            </div>
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-bold text-slate-700 leading-none">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-1">{{ Auth::user()->role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="fixed top-4 right-4 z-50 flex items-center px-4 py-3 bg-emerald-500 text-white text-sm font-medium rounded-xl shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition class="fixed top-4 right-4 z-50 flex items-center px-4 py-3 bg-red-500 text-white text-sm font-medium rounded-xl shadow-lg shadow-red-500/30">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                     x-transition role="alert" class="fixed top-4 right-4 z-50 flex items-start max-w-sm px-4 py-3 bg-red-500 text-white text-sm font-medium rounded-xl shadow-lg shadow-red-500/30">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <div class="flex-1">
                        <p class="font-bold">Data belum tersimpan:</p>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="ml-2 opacity-80 hover:opacity-100" aria-label="Tutup">&times;</button>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
