<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', __('ui.app.name')) }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100">
        <div class="min-h-screen flex flex-col">
            <header class="px-6 py-6 border-b border-slate-800">
                <div class="max-w-6xl mx-auto flex items-center justify-between pr-36">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold">{{ __('ui.app.name') }}</div>
                            <div class="text-xs text-slate-400">{{ __('ui.app.school') }} &bull; {{ __('ui.app.class') }}</div>
                        </div>
                    </div>

                    @if (Route::has('login'))
                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('ui.actions.open_dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-primary">{{ __('ui.actions.login') }}</a>
                            @endauth
                        </div>
                    @endif
                </div>
            </header>

            <main class="flex-1">
                <div class="max-w-6xl mx-auto px-6 py-16">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                        <div class="space-y-6">
                            <div class="inline-flex items-center gap-2 text-xs uppercase tracking-wide text-indigo-300 bg-indigo-500/10 border border-indigo-500/30 px-3 py-1 rounded-full">
                                {{ __('ui.welcome.hero_subtitle') }}
                            </div>
                            <h1 class="text-3xl md:text-4xl font-semibold leading-tight">
                                {{ __('ui.welcome.headline') }}
                            </h1>
                            <p class="text-slate-300">
                                {{ __('ui.welcome.hero_desc') }}
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <div class="glass-card px-4 py-3">
                                    <div class="text-xs text-slate-400">{{ __('ui.welcome.checkin_window') }}</div>
                                    <div class="text-lg font-semibold">05:45 - 07:10</div>
                                </div>
                                <div class="glass-card px-4 py-3">
                                    <div class="text-xs text-slate-400">{{ __('ui.welcome.checkout_window') }}</div>
                                    <div class="text-lg font-semibold">15:00 - 16:45</div>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card p-6 space-y-4">
                            <div class="text-sm text-slate-400">{{ __('ui.welcome.features_title') }}</div>
                            <ul class="space-y-3 text-sm text-slate-300">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-300">&bull;</span>
                                    {{ __('ui.welcome.feature_roles') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-300">&bull;</span>
                                    {{ __('ui.welcome.feature_permissions') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-300">&bull;</span>
                                    {{ __('ui.welcome.feature_recap') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-300">&bull;</span>
                                    {{ __('ui.welcome.feature_device') }}
                                </li>
                            </ul>
                            @auth
                                <a href="{{ route('dashboard') }}" class="btn-secondary w-full justify-center">{{ __('ui.actions.open_dashboard') }}</a>
                            @else
                                <a href="{{ route('login') }}" class="btn-secondary w-full justify-center">{{ __('ui.welcome.cta_login') }}</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </main>

            <div class="fixed top-4 right-4 z-50">
                <x-language-toggle />
            </div>
        </div>
    </body>
</html>
