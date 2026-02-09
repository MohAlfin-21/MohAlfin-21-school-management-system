<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.secretary.manage_attendance') }}: {{ $classroom->name }}
            </h2>
            <a href="{{ route('secretary.classroom.show', ['date' => $date]) }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6 space-y-4">
                <form class="flex items-end gap-3" method="GET" action="{{ route('secretary.attendance.index') }}">
                    <div>
                        <x-input-label for="date" :value="__('ui.labels.date')" />
                        <x-text-input id="date" name="date" type="date" class="block mt-1" :value="$date" />
                    </div>
                    <x-primary-button>{{ __('ui.actions.apply') }}</x-primary-button>
                </form>

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="py-2">{{ __('ui.labels.student') }}</th>
                                <th class="py-2">{{ __('ui.labels.status') }}</th>
                                <th class="py-2">{{ __('ui.labels.check_in') }}</th>
                                <th class="py-2">{{ __('ui.labels.check_out') }}</th>
                                <th class="py-2">{{ __('ui.secretary.manual_update') }}</th>
                                <th class="py-2">{{ __('ui.labels.early_checkout') }}</th>
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
                            @foreach ($rows as $row)
                                <tr class="align-top">
                                    <td class="py-2">
                                        <div class="font-medium">{{ $row['student']->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $row['student']->username }}</div>
                                    </td>
                                    <td class="py-2">
                                        <span class="badge {{ $statusStyles[$row['status']] ?? 'badge-neutral' }}">
                                            {{ __('ui.status.' . $row['status']) }}
                                        </span>
                                    </td>
                                    <td class="py-2">{{ $row['record']?->check_in_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2">{{ $row['record']?->check_out_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2">
                                        <form method="POST" action="{{ route('secretary.attendance.mark', $row['student']) }}" class="flex flex-col gap-2">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $date }}" />
                                            <select name="status" class="input-compact">
                                                @foreach (['present','late','absent','excused','sick'] as $status)
                                                    <option value="{{ $status }}" @selected($row['status'] === $status)>{{ __('ui.status.' . $status) }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="note" class="input-compact" placeholder="{{ __('ui.labels.notes') }}" value="{{ old('note') }}" />
                                            <button class="text-indigo-300 hover:text-indigo-200 text-sm text-left">{{ __('ui.actions.save') }}</button>
                                        </form>
                                    </td>
                                    <td class="py-2">
                                        @if ($row['record']?->check_in_at && ! $row['record']?->check_out_at)
                                            <form method="POST" action="{{ route('secretary.attendance.early-checkout', $row['student']) }}" class="flex flex-col gap-2">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date }}" />
                                                <input type="text" name="reason" class="input-compact" placeholder="{{ __('ui.labels.reason') }}" required />
                                                <button class="text-indigo-300 hover:text-indigo-200 text-sm text-left">{{ __('ui.actions.checkout') }}</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
