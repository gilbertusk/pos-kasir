@php
    $appName = config('app.name');
    $storeName = \App\Models\Setting::getValue('store_name') ?: $appName;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Masuk - {{ $appName }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-enamel-50 text-ink antialiased selection:bg-brand-100 selection:text-brand-900">
        <div class="flex min-h-screen items-center justify-center p-6">
            <div class="w-full sm:max-w-md">
                <div class="mb-8 flex flex-col items-center text-center">
                    {{-- Enamel plate mark: white plate, green rim --}}
                    <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white ring-[6px] ring-brand-800" aria-hidden="true">
                        <span class="h-8 w-8 rounded-full border-[3px] border-brand-300"></span>
                    </span>
                    <h1 class="text-2xl font-bold text-ink">{{ $appName }}</h1>
                    <p class="mt-1 text-sm text-enamel-500">
                        {{ $storeName !== $appName ? "Masuk ke kasir {$storeName}" : 'Masuk untuk mulai melayani pesanan' }}
                    </p>
                </div>

                <div class="rounded-2xl border border-enamel-200 bg-white p-6 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
