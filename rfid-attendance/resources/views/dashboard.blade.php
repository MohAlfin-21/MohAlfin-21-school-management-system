<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="glass-card p-6">
                <div class="text-slate-300">{{ __('ui.dashboard.welcome') }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
