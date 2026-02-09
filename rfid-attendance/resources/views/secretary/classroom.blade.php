<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.secretary_dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (! $isAssignedSecretary)
                <div class="alert alert-warning">
                    {{ __('ui.messages.secretary_not_assigned') }}
                </div>
            @elseif (! $isActiveSecretary)
                <div class="alert alert-warning">
                    {{ __('ui.messages.secretary_not_active') }}
                </div>
            @endif
            <div class="glass-card p-6 space-y-4">
                <div class="flex flex-wrap items-end gap-3">
                    <form class="flex items-end gap-3" method="GET" action="{{ route('secretary.classroom.show') }}">
                        <div>
                            <x-input-label for="date" :value="__('ui.labels.date')" />
                            <x-text-input id="date" name="date" type="date" class="block mt-1" :value="$date" />
                        </div>
                        <x-primary-button>{{ __('ui.actions.apply') }}</x-primary-button>
                    </form>

                    <div class="ms-auto flex gap-2">
                        <a class="btn-primary" href="{{ route('secretary.attendance.index', ['date' => $date]) }}">
                            {{ __('ui.secretary.manage_attendance') }}
                        </a>
                        <a class="btn-secondary" href="{{ route('secretary.absence-requests.index') }}">
                            {{ __('ui.secretary.pending_requests') }} ({{ $pendingRequests }})
                        </a>
                    </div>
                </div>

                <div class="text-slate-300">
                    {{ __('ui.classrooms.title') }}: <span class="font-semibold">{{ $classroom->name }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.dashboard.total_students') }}</div>
                        <div class="text-xl font-semibold">{{ $totalStudents }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.checked_in') }}</div>
                        <div class="text-xl font-semibold">{{ $checkedIn }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.not_checked_in') }}</div>
                        <div class="text-xl font-semibold">{{ $notCheckedIn }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.not_checked_out') }}</div>
                        <div class="text-xl font-semibold">{{ $notCheckedOut }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
