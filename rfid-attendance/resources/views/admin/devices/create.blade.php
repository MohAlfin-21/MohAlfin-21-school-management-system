<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.devices.new') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="glass-card p-6 space-y-6">
                <form method="POST" action="{{ route('admin.devices.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('ui.labels.name')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="location" :value="__('ui.labels.location')" />
                        <x-text-input id="location" name="location" class="block mt-1 w-full" :value="old('location')" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" name="is_active" value="1" type="checkbox" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60" checked />
                        <label for="is_active" class="text-sm text-slate-300">{{ __('ui.status.active') }}</label>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.devices.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.cancel') }}</a>
                        <x-primary-button>{{ __('ui.actions.create') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
