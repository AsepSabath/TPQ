<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-100">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 via-white to-slate-100"></div>
            <div class="absolute -top-24 left-1/2 h-64 w-64 -translate-x-1/2 rounded-full bg-emerald-100/60 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-sky-100/60 blur-3xl"></div>

            <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col items-center gap-3 text-center">
                    <a href="/" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-16 w-auto sm:h-18" />
                    </a>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">الموسسةدارالاستقامةقرأنية</p>
                        <p class="mt-1 text-sm text-slate-600">YAYASAN DARUL ISTIQOMAH QUR’ANIYYAH</p>
                    </div>
                </div>

                <div class="w-full max-w-md rounded-2xl border border-white/60 bg-white/95 px-6 py-7 shadow-xl shadow-slate-200/70 backdrop-blur">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
