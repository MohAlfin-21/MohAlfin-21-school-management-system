<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.actions.request_permission') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="glass-card p-6 space-y-6">
                <form method="POST" action="{{ route('me.absence-requests.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="start_date" :value="__('ui.labels.start_date')" />
                            <x-text-input id="start_date" name="start_date" type="date" class="block mt-1 w-full" :value="old('start_date', now()->toDateString())" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="end_date" :value="__('ui.labels.end_date')" />
                            <x-text-input id="end_date" name="end_date" type="date" class="block mt-1 w-full" :value="old('end_date', now()->toDateString())" required />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="type" :value="__('ui.labels.type')" />
                        <select id="type" name="type" class="input-base" required>
                            <option value="permission" @selected(old('type') === 'permission')>{{ __('ui.absence.type_permission') }}</option>
                            <option value="sick" @selected(old('type') === 'sick')>{{ __('ui.absence.type_sick') }}</option>
                            <option value="other" @selected(old('type') === 'other')>{{ __('ui.absence.type_other') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="reason_text" :value="__('ui.labels.reason_optional')" />
                        <textarea id="reason_text" name="reason_text" class="input-base" rows="3">{{ old('reason_text') }}</textarea>
                        <x-input-error :messages="$errors->get('reason_text')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="file" :value="__('ui.absence.upload_letter')" />
                        <input id="file" name="file" type="file" accept="image/png,image/jpeg" class="mt-1 block w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-800 file:px-4 file:py-2 file:text-slate-200 hover:file:bg-slate-700" required />
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('me.attendance.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.cancel') }}</a>
                        <x-primary-button>{{ __('ui.actions.submit') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
