@php
    $appName = config('app.name');
    $storeName = \App\Models\Setting::getValue('store_name') ?: $appName;
    $isKasirScreen = request()->routeIs('kasir.*');
    $lowStock = \App\Models\Product::lowStock()->count();
    $initials = strtoupper(substr(Auth::user()->name, 0, 2));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir') - {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{-- The kasir screen starts with the sidebar collapsed to an icon rail so products and the order get the room. --}}
<body class="min-h-screen bg-enamel-50 text-ink"
      x-data="{ sidebarOpen: {{ $isKasirScreen ? 'false' : 'true' }}, navOpen: false }"
      @keydown.escape.window="navOpen = false">
    <div class="flex min-h-screen">
        {{-- Mobile backdrop --}}
        <div x-show="navOpen" x-transition.opacity x-cloak class="fixed inset-0 z-40 bg-ink/50 lg:hidden" @click="navOpen = false"></div>

        {{-- Sidebar --}}
        <aside :class="[sidebarOpen ? 'lg:w-64' : 'lg:w-[76px]', navOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
               class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-brand-800 text-brand-100 transition-[width,transform] duration-300">
            {{-- Store --}}
            <div class="flex h-16 items-center gap-3 border-b border-white/10 px-4">
                <a href="{{ route('kasir.index') }}" class="flex min-w-0 flex-1 items-center gap-3" title="{{ $appName }} · {{ $storeName }}">
                    {{-- Enamel plate mark: white plate, green rim --}}
                    <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-white ring-4 ring-brand-600" aria-hidden="true">
                        <span class="h-5 w-5 rounded-full border-2 border-brand-300"></span>
                    </span>
                    <span class="min-w-0" :class="sidebarOpen ? '' : 'lg:hidden'">
                        <span class="block truncate text-[15px] font-bold leading-tight text-white">{{ $appName }}</span>
                        <span class="block truncate text-xs text-brand-200">{{ $storeName !== $appName ? $storeName : 'Sistem kasir' }}</span>
                    </span>
                </a>
                <button type="button" @click="navOpen = false" class="rounded-lg p-2 text-brand-200 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 overflow-y-auto p-3" aria-label="Menu utama">
                @php
                    $navItems = [
                        ['route' => 'kasir.index', 'label' => 'Kasir', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
                        ['route' => 'products.index', 'ownerOnly' => true, 'label' => 'Inventaris', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
                        ['route' => 'transactions.index', 'label' => 'Transaksi', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                        ['route' => 'reports.index', 'ownerOnly' => true, 'label' => 'Analitik', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
                        ['route' => 'expenses.index', 'ownerOnly' => true, 'label' => 'Pengeluaran', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>'],
                        ['route' => 'settings.index', 'ownerOnly' => true, 'label' => 'Pengaturan', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @continue(($item['ownerOnly'] ?? false) && ! Auth::user()->isOwner())
                    @php $active = request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                    <a href="{{ route($item['route']) }}"
                       title="{{ $item['label'] }}"
                       @if($active) aria-current="page" @endif
                       class="relative flex min-h-[44px] items-center gap-3 rounded-xl px-3 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-kunyit-300
                              {{ $active ? 'bg-white/10 font-semibold text-white' : 'text-brand-200 hover:bg-white/5 hover:text-white' }}">
                        @if($active)
                            <span class="absolute inset-y-2 left-0 w-1 rounded-r bg-kunyit-400" aria-hidden="true"></span>
                        @endif
                        <svg class="h-5 w-5 flex-shrink-0 {{ $active ? 'text-kunyit-300' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $item['icon'] !!}</svg>
                        <span class="truncate text-sm" :class="sidebarOpen ? '' : 'lg:sr-only'">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Account --}}
            <div class="space-y-1 border-t border-white/10 p-3">
                <a href="{{ route('profile.edit') }}" title="Profil saya" class="flex min-h-[44px] items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-kunyit-300">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-semibold text-white">{{ $initials }}</span>
                    <span class="min-w-0" :class="sidebarOpen ? '' : 'lg:sr-only'">
                        <span class="block truncate text-sm font-medium text-white">{{ Auth::user()->name }}</span>
                        <span class="block truncate text-xs text-brand-200">{{ Auth::user()->isOwner() ? 'Owner' : 'Kasir' }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="flex min-h-[44px] w-full items-center gap-3 rounded-xl px-3 text-brand-200 transition-colors hover:bg-sambal-700/20 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-kunyit-300">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="text-sm" :class="sidebarOpen ? '' : 'lg:sr-only'">Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <div :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-[76px]'" class="flex min-h-screen min-w-0 flex-1 flex-col transition-[margin] duration-300">
            {{-- Header --}}
            <header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-enamel-200 bg-white/90 px-4 backdrop-blur md:px-6">
                <button type="button" @click="navOpen = true" class="-ml-1 rounded-lg p-2 text-enamel-600 hover:bg-enamel-100 lg:hidden" aria-label="Buka menu">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <button type="button" @click="sidebarOpen = !sidebarOpen" class="hidden rounded-lg p-2 text-enamel-500 hover:bg-enamel-100 hover:text-ink lg:inline-flex"
                        :aria-label="sidebarOpen ? 'Ciutkan menu' : 'Lebarkan menu'" :aria-expanded="sidebarOpen.toString()">
                    <svg class="h-5 w-5 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-lg font-bold leading-tight text-ink">@yield('title', 'Kasir')</h1>
                    @hasSection('subtitle')
                        <p class="truncate text-xs text-enamel-500">@yield('subtitle')</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 md:gap-4">
                    {{-- Low stock: owners jump to inventory, kasir just sees the count --}}
                    @if($lowStock > 0)
                        @php $lowStockLabel = $lowStock . ' barang stoknya menipis'; @endphp
                        @if(Auth::user()->isOwner())
                            <a href="{{ route('products.index') }}" title="{{ $lowStockLabel }}" class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-kunyit-200 bg-kunyit-50 px-3 text-sm font-medium text-kunyit-800 hover:bg-kunyit-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                <span class="tabular">{{ $lowStock }}</span><span class="hidden sm:inline">stok menipis</span>
                            </a>
                        @else
                            <span title="{{ $lowStockLabel }}" class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-kunyit-200 bg-kunyit-50 px-3 text-sm font-medium text-kunyit-800">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                <span class="tabular">{{ $lowStock }}</span><span class="hidden sm:inline">stok menipis</span>
                            </span>
                        @endif
                    @endif

                    <span x-data="{ time: '' }"
                          x-init="const tick = () => time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB'; tick(); setInterval(tick, 15000)"
                          x-text="time"
                          class="tabular hidden text-sm text-enamel-500 md:inline"></span>
                </div>
            </header>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition.opacity role="status"
                     class="fixed right-4 top-4 z-[60] flex max-w-sm items-center gap-2 rounded-xl bg-brand-800 px-4 py-3 text-sm font-medium text-white shadow-lg">
                    <svg class="h-5 w-5 flex-shrink-0 text-kunyit-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity role="alert"
                     class="fixed right-4 top-4 z-[60] flex max-w-sm items-center gap-2 rounded-xl bg-sambal-700 px-4 py-3 text-sm font-medium text-white shadow-lg">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)" x-transition.opacity role="alert"
                     class="fixed right-4 top-4 z-[60] flex max-w-sm items-start gap-2 rounded-xl bg-sambal-700 px-4 py-3 text-sm text-white shadow-lg">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <div class="flex-1">
                        <p class="font-semibold">Data belum tersimpan:</p>
                        <ul class="list-inside list-disc">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="-mr-1 rounded p-1 text-white/80 hover:text-white" aria-label="Tutup">&times;</button>
                </div>
            @endif

            {{-- Page Content --}}
            <main class="@yield('mainClass', 'p-4 md:p-6') flex-1">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
