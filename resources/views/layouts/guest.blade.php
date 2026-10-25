<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ManuAleja ERP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                margin: 0;
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(180deg, #edf9f0 0%, #f8fafc 100%);
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-800">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
            <div class="mb-6 flex items-center gap-3">
                @if (file_exists(public_path('build/images/logo-lyra.png')))
                    <img src="{{ asset('build/images/logo-lyra.png') }}" alt="Logo LYRA" class="h-14 w-14 object-contain" />
                @elseif (file_exists(public_path('build/images/logo-lyra.svg')))
                    <img src="{{ asset('build/images/logo-lyra.svg') }}" alt="Logo LYRA" class="h-14 w-14 object-contain" />
                @else
                    <x-application-logo class="h-14 w-14" />
                @endif
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#15803d]">Sistema</div>
                    <div class="text-lg font-bold leading-tight text-[#14532d]">LYRA ERP</div>
                </div>
            </div>

            <div class="w-full overflow-hidden rounded-2xl border border-[#14532d]/10 bg-white px-6 py-6 shadow-md sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>