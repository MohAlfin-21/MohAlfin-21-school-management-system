<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.my_profile') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6 space-y-6">
                <div>
                    <div class="text-slate-400 text-sm">{{ __('ui.labels.name') }}</div>
                    <div class="font-semibold">{{ $user->name }}</div>
                    <div class="text-sm text-slate-300">{{ __('ui.labels.username') }}: {{ $user->username }}</div>
                </div>

                <form method="POST" action="{{ route('me.profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="phone" :value="__('ui.labels.phone')" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $profile->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('ui.labels.address')" />
                        <textarea id="address" name="address" class="input-base" rows="3">{{ old('address', $profile->address) }}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="parent_name" :value="__('ui.labels.parent_name')" />
                            <x-text-input id="parent_name" name="parent_name" class="block mt-1 w-full" :value="old('parent_name', $profile->parent_name)" />
                            <x-input-error :messages="$errors->get('parent_name')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="parent_phone" :value="__('ui.labels.parent_phone')" />
                            <x-text-input id="parent_phone" name="parent_phone" class="block mt-1 w-full" :value="old('parent_phone', $profile->parent_phone)" />
                            <x-input-error :messages="$errors->get('parent_phone')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('students.profile', $user) }}" class="text-indigo-300 hover:text-indigo-200">{{ __('ui.actions.view_profile') }}</a>
                        <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
