<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.rfid.card.edit') }}
            </h2>
            <form id="delete-rfid-card-form" method="POST" action="{{ route('admin.rfid-cards.destroy', $card) }}">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-danger" x-on:click="$dispatch('open-modal', 'confirm-delete-rfid-card')">
                    {{ __('ui.actions.delete') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-modal name="confirm-delete-rfid-card">
                <div class="p-6 space-y-4">
                    <div class="text-lg font-semibold text-white">{{ __('ui.actions.delete') }}</div>
                    <div class="text-sm text-slate-300">{{ __('ui.actions.delete') }}?</div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn-secondary" x-on:click="$dispatch('close-modal', 'confirm-delete-rfid-card')">
                            {{ __('ui.actions.cancel') }}
                        </button>
                        <button type="button" class="btn-danger" x-on:click="document.getElementById('delete-rfid-card-form').submit()">
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
                <form method="POST" action="{{ route('admin.rfid-cards.update', $card) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="uid" :value="__('ui.rfid.card.uid')" />
                        <x-text-input id="uid" name="uid" class="block mt-1 w-full" :value="old('uid', $card->uid)" required />
                        <x-input-error :messages="$errors->get('uid')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="user_id" :value="__('ui.rfid.card.student')" />
                        <select id="user_id" name="user_id" class="input-base" required>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected((string) old('user_id', $card->user_id) === (string) $student->id)>
                                    {{ $student->name }} ({{ $student->username }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('ui.rfid.card.status')" />
                        <select id="status" name="status" class="input-base" required>
                            <option value="active" @selected(old('status', $card->status) === 'active')>{{ __('ui.status.active') }}</option>
                            <option value="lost" @selected(old('status', $card->status) === 'lost')>{{ __('ui.status.lost') }}</option>
                            <option value="inactive" @selected(old('status', $card->status) === 'inactive')>{{ __('ui.status.inactive') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.rfid-cards.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
                        <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
