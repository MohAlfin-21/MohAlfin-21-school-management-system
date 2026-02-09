<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.students.profile_title') }}
            </h2>
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="glass-card p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <div class="text-2xl font-semibold">{{ $student->name }}</div>
                        <div class="text-sm text-slate-400">{{ __('ui.labels.username') }}: {{ $student->username }}</div>
                        <div class="text-sm text-slate-400">{{ __('ui.classrooms.title') }}: {{ $classroom?->name ?? '-' }}</div>
                    </div>

                    @if (auth()->id() === $student->id)
                        <a class="btn-primary" href="{{ route('me.profile.edit') }}">
                            {{ __('ui.students.edit_my_profile') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="font-semibold mb-4">{{ __('ui.students.personal_info') }}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-400">NISN</div>
                        <div class="font-medium">{{ $profile->nisn ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">{{ __('ui.labels.phone') }}</div>
                        <div class="font-medium">{{ $profile->phone ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">{{ __('ui.students.birth') }}</div>
                        <div class="font-medium">
                            {{ $profile->birth_place ?? '-' }}
                            @if ($profile->birth_date)
                                , {{ $profile->birth_date->format('d M Y') }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-400">{{ __('ui.students.gender') }}</div>
                        <div class="font-medium">{{ $profile->gender ?? '-' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-slate-400">{{ __('ui.labels.address') }}</div>
                        <div class="font-medium">{{ $profile->address ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="font-semibold mb-4">{{ __('ui.students.parent_info') }}</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-400">{{ __('ui.labels.parent_name') }}</div>
                        <div class="font-medium">{{ $profile->parent_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">{{ __('ui.labels.parent_phone') }}</div>
                        <div class="font-medium">{{ $profile->parent_phone ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
