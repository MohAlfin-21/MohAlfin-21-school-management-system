<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.my_attendance') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-slate-400 text-sm">{{ __('ui.student.today') }}</div>
                        <div class="font-semibold">{{ $today }}</div>
                        <div class="text-sm text-slate-300">{{ __('ui.classrooms.title') }}: {{ $classroom?->name ?? '-' }}</div>
                    </div>
                    <a class="btn-primary" href="{{ route('me.absence-requests.create') }}">
                        {{ __('ui.actions.request_permission') }}
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.labels.status') }}</div>
                        <div class="text-lg font-semibold">
                            @php
                                $todayStatus = $todayRecord?->status;
                            @endphp
                            <span class="badge {{ in_array($todayStatus, ['present','late','absent','excused','sick'], true) ? ($todayStatus === 'present' ? 'badge-success' : ($todayStatus === 'absent' ? 'badge-danger' : 'badge-warning')) : 'badge-neutral' }}">
                                {{ $todayStatus ? __('ui.status.' . $todayStatus) : __('ui.student.not_yet') }}
                            </span>
                        </div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.labels.check_in') }}</div>
                        <div class="text-lg font-semibold">{{ $todayRecord?->check_in_at?->format('H:i') ?? '-' }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.labels.check_out') }}</div>
                        <div class="text-lg font-semibold">{{ $todayRecord?->check_out_at?->format('H:i') ?? '-' }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.labels.notes') }}</div>
                        <div class="text-sm">{{ $todayRecord?->note ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="font-semibold mb-4">{{ __('ui.student.history') }}</div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="py-2">{{ __('ui.labels.date') }}</th>
                                <th class="py-2">{{ __('ui.labels.status') }}</th>
                                <th class="py-2">{{ __('ui.labels.check_in') }}</th>
                                <th class="py-2">{{ __('ui.labels.check_out') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusStyles = [
                                    'present' => 'badge-success',
                                    'late' => 'badge-warning',
                                    'absent' => 'badge-danger',
                                    'excused' => 'badge-warning',
                                    'sick' => 'badge-warning',
                                ];
                            @endphp
                            @foreach ($history as $record)
                                <tr>
                                    <td class="py-2">{{ $record->date->toDateString() }}</td>
                                    <td class="py-2">
                                        <span class="badge {{ $statusStyles[$record->status] ?? 'badge-neutral' }}">
                                            {{ __('ui.status.' . $record->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2">{{ $record->check_in_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2">{{ $record->check_out_at?->format('H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
