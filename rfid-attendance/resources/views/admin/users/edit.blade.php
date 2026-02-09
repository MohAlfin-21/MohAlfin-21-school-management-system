<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.users.edit') }}
            </h2>
            <form id="deactivate-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-danger" x-on:click="$dispatch('open-modal', 'confirm-deactivate-user')">
                    {{ __('ui.users.deactivate') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-modal name="confirm-deactivate-user">
                <div class="p-6 space-y-4">
                    <div class="text-lg font-semibold text-white">{{ __('ui.users.deactivate') }}</div>
                    <div class="text-sm text-slate-300">{{ __('ui.users.deactivate_confirm') }}</div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" x-on:click="$dispatch('close-modal', 'confirm-deactivate-user')">
                            {{ __('ui.actions.cancel') }}
                        </button>
                        <button type="button" class="btn-danger" x-on:click="document.getElementById('deactivate-user-form').submit()">
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

            <div class="glass-card p-6 space-y-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" :value="__('ui.labels.name')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="username" :value="__('ui.labels.username')" />
                        <x-text-input id="username" name="username" class="block mt-1 w-full" :value="old('username', $user->username)" required />
                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('ui.labels.email_optional')" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $user->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('ui.labels.password_new_optional')" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_active" name="is_active" value="1" type="checkbox" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60"
                            @checked(old('is_active', $user->is_active) ? true : false) />
                        <label for="is_active" class="text-sm text-slate-300">{{ __('ui.status.active') }}</label>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-slate-300">{{ __('ui.labels.role') }}</div>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach ($roles as $role)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60"
                                        @checked(in_array($role->name, old('roles', $user->roles->pluck('name')->all()), true)) />
                                    <span class="text-sm text-slate-300">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('roles')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
                        <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
