<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased text-slate-800 bg-slate-50 selection:bg-primary-100 selection:text-primary-900">
        <div class="min-h-screen flex items-center justify-center p-6 relative overflow-hidden">
            {{-- Background Elements --}}
            <div class="absolute top-0 left-0 w-full h-full pointer-events-none z-0">
                <div class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-primary-100 rounded-full blur-[120px] opacity-40"></div>
                <div class="absolute -bottom-[20%] -right-[10%] w-[60%] h-[60%] bg-indigo-100 rounded-full blur-[120px] opacity-40"></div>
            </div>

            <div class="w-full sm:max-w-md relative z-10">
                {{-- Logo/Brand --}}
                <div class="flex flex-col items-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-600 to-indigo-700 rounded-2xl flex items-center justify-center shadow-xl shadow-primary-200 mb-4 transform hover:scale-105 transition-transform">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Indigo <span class="text-primary-600">POS</span></h1>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1">Management System</p>
                </div>

                {{-- Login Card --}}
                <div class="bg-white p-8 rounded-[2rem] shadow-2xl shadow-slate-200/50 border border-slate-100">
                    {{ $slot }}
                </div>

                {{-- Footer Info --}}
                <p class="text-center mt-8 text-[10px] font-black text-slate-400 uppercase tracking-widest italic opacity-50">
                    &copy; {{ date('Y') }} Indigo POS &bull; Premium Experience
                </p>
            </div>
        </div>
    </body>
</html>

