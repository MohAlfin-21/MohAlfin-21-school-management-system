<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.dashboard.admin') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="glass-card p-5">
                    <div class="text-slate-400 text-sm">{{ __('ui.dashboard.total_students') }}</div>
                    <div class="text-3xl font-semibold">{{ $counts['students'] }}</div>
                    <a class="text-indigo-300 text-sm" href="{{ route('admin.users.index') }}">{{ __('ui.actions.manage_students') }}</a>
                </div>
                <div class="glass-card p-5">
                    <div class="text-slate-400 text-sm">{{ __('ui.dashboard.total_teachers') }}</div>
                    <div class="text-3xl font-semibold">{{ $counts['teachers'] }}</div>
                    <a class="text-indigo-300 text-sm" href="{{ route('admin.users.index') }}">{{ __('ui.actions.manage_teachers') }}</a>
                </div>
                <div class="glass-card p-5">
                    <div class="text-slate-400 text-sm">{{ __('ui.dashboard.classrooms') }}</div>
                    <div class="text-3xl font-semibold">{{ $counts['classrooms'] }}</div>
                    <a class="text-indigo-300 text-sm" href="{{ route('admin.classrooms.index') }}">{{ __('ui.actions.manage_classrooms') }}</a>
                </div>
            </div>

            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-semibold">{{ __('ui.dashboard.attendance_settings_title') }}</div>
                        <div class="text-sm text-slate-400">{{ __('ui.dashboard.attendance_settings_desc') }}</div>
                    </div>
                    <a class="btn-primary" href="{{ route('admin.settings.attendance.edit') }}">
                        {{ __('ui.actions.open') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
