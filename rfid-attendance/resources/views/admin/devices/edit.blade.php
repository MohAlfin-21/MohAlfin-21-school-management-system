<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.devices.edit') }}
            </h2>
            <div class="inline-flex items-center gap-2">
                <form id="regenerate-device-form" method="POST" action="{{ route('admin.devices.regenerate-token', $device) }}">
                    @csrf
                    <button type="button" class="btn-secondary" x-on:click="$dispatch('open-modal', 'confirm-regenerate-device')">
                        {{ __('ui.actions.regenerate_token') }}
                    </button>
                </form>
                <form id="delete-device-form" method="POST" action="{{ route('admin.devices.destroy', $device) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-danger" x-on:click="$dispatch('open-modal', 'confirm-delete-device')">
                        {{ __('ui.actions.delete') }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-modal name="confirm-regenerate-device">
                <div class="p-6 space-y-4">
                    <div class="text-lg font-semibold text-white">{{ __('ui.actions.regenerate_token') }}</div>
                    <div class="text-sm text-slate-300">{{ __('ui.devices.regenerate_token_confirm') }}</div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" x-on:click="$dispatch('close-modal', 'confirm-regenerate-device')">
                            {{ __('ui.actions.cancel') }}
                        </button>
                        <button type="button" class="btn-primary" x-on:click="document.getElementById('regenerate-device-form').submit()">
                            {{ __('ui.actions.confirm') }}
                        </button>
                    </div>
                </div>
            </x-modal>

            <x-modal name="confirm-delete-device">
                <div class="p-6 space-y-4">
                    <div class="text-lg font-semibold text-white">{{ __('ui.actions.delete') }}</div>
                    <div class="text-sm text-slate-300">{{ __('ui.actions.delete') }}?</div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" x-on:click="$dispatch('close-modal', 'confirm-delete-device')">
                            {{ __('ui.actions.cancel') }}
                        </button>
                        <button type="button" class="btn-danger" x-on:click="document.getElementById('delete-device-form').submit()">
                            {{ __('ui.actions.confirm') }}
                        </button>
                    </div>
                </div>
            </x-modal>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6 space-y-4 relative" x-data="{ showToken: false, copied: false, copy() { navigator.clipboard.writeText('{{ $device->token_plain }}').then(() => { this.copied = true; setTimeout(() => this.copied = false, 1500); }); } }" x-cloak>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold">{{ __('ui.labels.device_token') }}</div>
                        <p class="text-sm text-slate-400 mt-1">
                            {{ __('ui.labels.device_token_hint') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-700 bg-slate-800/70 text-slate-100 hover:bg-slate-700/80"
                            @click="showToken = !showToken" :aria-label="showToken ? '{{ __('ui.actions.hide') }}' : '{{ __('ui.actions.show') }}'">
                            <svg x-show="!showToken" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                            </svg>
                            <svg x-show="showToken" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 0 0 2.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 0 0 3.654-.684M6.228 6.228A9.705 9.705 0 0 1 12 5.25c6 0 9.75 6.75 9.75 6.75a10.49 10.49 0 0 1-2.178 2.727M6.228 6.228 3 3m3.228 3.228 3.62 3.62m4.772 4.772 3.123 3.123m-3.123-3.123-3.62-3.62m0 0a2.25 2.25 0 1 0 3.182-3.182" />
                            </svg>
                        </button>
                        @if ($device->token_plain)
                            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-700 bg-slate-800/70 text-slate-100 hover:bg-slate-700/80"
                                @click="copy()" aria-label="{{ __('ui.actions.copy') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 9.75A2.25 2.25 0 0 1 11.25 7.5h6A2.25 2.25 0 0 1 19.5 9.75v6A2.25 2.25 0 0 1 17.25 18h-6A2.25 2.25 0 0 1 9 15.75v-6Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 15.75h-1.5A2.25 2.25 0 0 1 3 13.5v-6A2.25 2.25 0 0 1 4.5 5.25h6A2.25 2.25 0 0 1 12.75 7.5v1.5" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                @if (session('created_device_token'))
                    <div class="alert alert-warning">
                        <div class="font-semibold">{{ __('ui.labels.device_token') }}</div>
                        <div class="font-mono break-all">{{ session('created_device_token') }}</div>
                    </div>
                @endif

                @if ($device->token_plain)
                    <div class="font-mono break-all px-3 py-2 rounded bg-slate-900/60 border border-slate-700/60">
                        <span x-show="showToken" x-text="'{{ $device->token_plain }}'"></span>
                        <span x-show="!showToken" class="select-none tracking-widest">************************</span>
                    </div>
                @else
                    <div class="text-sm text-slate-400">
                        {{ __('ui.labels.device_token_missing') }}
                    </div>
                @endif

                @if ($device->token_plain)
                    <div x-show="copied" x-transition class="absolute right-4 -bottom-3 translate-y-full text-xs bg-amber-200/90 text-amber-900 px-3 py-1 rounded shadow">
                        {{ __('ui.labels.copy_success') }}
                    </div>
                @endif
            </div>

            <div class="glass-card p-6 space-y-6">
                <form method="POST" action="{{ route('admin.devices.update', $device) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('ui.labels.name')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $device->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="location" :value="__('ui.labels.location')" />
                        <x-text-input id="location" name="location" class="block mt-1 w-full" :value="old('location', $device->location)" />
                        <x-input-error :messages="$errors->get('location')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" name="is_active" value="1" type="checkbox" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60"
                            @checked(old('is_active', $device->is_active) ? true : false) />
                        <label for="is_active" class="text-sm text-slate-300">{{ __('ui.status.active') }}</label>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.devices.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
                        <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
