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
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-slate-950 text-slate-100">
            <div class="flex min-h-screen">
                <aside class="hidden md:flex md:flex-col w-64 bg-slate-900/70 border-r border-slate-800">
                    @include('layouts.partials.sidebar')
                </aside>

                <div class="flex-1 flex flex-col min-h-screen">
                    <header class="sticky top-0 z-10 h-16 bg-slate-900/70 border-b border-slate-800 backdrop-blur">
                        <div class="h-full flex items-center gap-4 px-4 pr-36">
                            <button class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg bg-slate-800/60 text-slate-200" @click="sidebarOpen = true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>

                            <div class="flex-1">
                                @isset($header)
                                    {{ $header }}
                                @else
                                    <div class="text-lg font-semibold">{{ __('ui.nav.dashboard') }}</div>
                                @endisset
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right hidden sm:block">
                                    <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-slate-400">{{ Auth::user()->username }}</div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="inline-flex items-center px-3 py-2 bg-slate-800/60 border border-slate-700 rounded-lg text-xs text-slate-200 hover:bg-slate-700/60">
                                        {{ __('ui.actions.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </header>

                    <main class="flex-1 p-6">
                        {{ $slot }}
                    </main>
                </div>
            </div>

            <div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-black/60 z-40 md:hidden" @click="sidebarOpen = false"></div>
            <aside x-show="sidebarOpen" x-cloak class="fixed inset-y-0 left-0 w-64 bg-slate-900/90 border-r border-slate-800 z-50 md:hidden">
                <div class="flex justify-end p-4">
                    <button class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-800/60 text-slate-200" @click="sidebarOpen = false">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @include('layouts.partials.sidebar')
            </aside>

            <div class="fixed top-4 right-4 z-50">
                <x-language-toggle />
            </div>
        </div>
    </body>
</html>
