<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.attendance_settings') }}
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
                <form method="POST" action="{{ route('admin.settings.attendance.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="timezone" :value="__('ui.labels.timezone')" />
                        <x-text-input id="timezone" name="timezone" class="block mt-1 w-full" :value="old('timezone', $settings->timezone)" required />
                        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="check_in_start" :value="__('ui.labels.check_in_start')" />
                            <x-text-input id="check_in_start" name="check_in_start" class="block mt-1 w-full" :value="old('check_in_start', $settings->check_in_start)" required />
                            <x-input-error :messages="$errors->get('check_in_start')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="check_in_end" :value="__('ui.labels.check_in_end')" />
                            <x-text-input id="check_in_end" name="check_in_end" class="block mt-1 w-full" :value="old('check_in_end', $settings->check_in_end)" required />
                            <x-input-error :messages="$errors->get('check_in_end')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="check_out_start" :value="__('ui.labels.check_out_start')" />
                            <x-text-input id="check_out_start" name="check_out_start" class="block mt-1 w-full" :value="old('check_out_start', $settings->check_out_start)" required />
                            <x-input-error :messages="$errors->get('check_out_start')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="check_out_end" :value="__('ui.labels.check_out_end')" />
                            <x-text-input id="check_out_end" name="check_out_end" class="block mt-1 w-full" :value="old('check_out_end', $settings->check_out_end)" required />
                            <x-input-error :messages="$errors->get('check_out_end')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="late_after" :value="__('ui.labels.late_after')" />
                        <x-text-input id="late_after" name="late_after" class="block mt-1 w-full" :value="old('late_after', $settings->late_after)" />
                        <x-input-error :messages="$errors->get('late_after')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="max_upload_mb" :value="__('ui.labels.max_upload_mb')" />
                            <x-text-input id="max_upload_mb" name="max_upload_mb" type="number" class="block mt-1 w-full" :value="old('max_upload_mb', $settings->max_upload_mb)" required />
                            <x-input-error :messages="$errors->get('max_upload_mb')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="allowed_mimes" :value="__('ui.labels.allowed_mimes')" />
                            <x-text-input id="allowed_mimes" name="allowed_mimes" class="block mt-1 w-full" :value="old('allowed_mimes', $settings->allowed_mimes)" required />
                            <x-input-error :messages="$errors->get('allowed_mimes')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
                        <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
