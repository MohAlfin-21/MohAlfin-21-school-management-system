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
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center bg-slate-950 text-slate-100">
            <div class="mb-6 text-center">
                <a href="/" class="inline-flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                        </svg>
                    </div>
                    <div class="text-lg font-semibold">{{ __('ui.app.name') }}</div>
                </a>
                <div class="text-xs text-slate-400 mt-1">{{ __('ui.app.school') }} &bull; {{ __('ui.app.class') }}</div>
            </div>

            <div class="w-full sm:max-w-md px-6 py-6 glass-card">
                {{ $slot }}
            </div>
        </div>

        <div class="fixed top-4 right-4 z-50">
            <x-language-toggle />
        </div>
    </body>
</html>
