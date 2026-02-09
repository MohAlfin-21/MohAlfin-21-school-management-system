<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.teacher.attendance_title') }}: {{ $classroom->name }}
            </h2>
            <a href="{{ route('teacher.classes.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="glass-card p-6 space-y-4">
                <form class="flex flex-wrap items-end gap-3" method="GET" action="{{ route('teacher.classes.show', $classroom) }}">
                    <div>
                        <x-input-label for="date" :value="__('ui.labels.date')" />
                        <x-text-input id="date" name="date" type="date" class="block mt-1" :value="$date" />
                    </div>
                    <x-primary-button>{{ __('ui.actions.apply') }}</x-primary-button>
                    <a class="btn-secondary" href="{{ route('teacher.classes.show', [$classroom, 'date' => $date, 'export' => 'excel']) }}">
                        {{ __('ui.actions.export_excel') }}
                    </a>
                </form>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.present') }}</div>
                        <div class="text-xl font-semibold">{{ $summary['present'] }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.late') }}</div>
                        <div class="text-xl font-semibold">{{ $summary['late'] }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.absent') }}</div>
                        <div class="text-xl font-semibold">{{ $summary['absent'] }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.excused') }}</div>
                        <div class="text-xl font-semibold">{{ $summary['excused'] }}</div>
                    </div>
                    <div class="glass-card p-3">
                        <div class="text-xs text-slate-400">{{ __('ui.status.sick') }}</div>
                        <div class="text-xl font-semibold">{{ $summary['sick'] }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <canvas id="summaryChart" height="100"></canvas>
                </div>
            </div>

            <div class="glass-card p-6">
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="py-2">{{ __('ui.labels.student') }}</th>
                                <th class="py-2">{{ __('ui.labels.username') }}</th>
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
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="py-2">
                                        <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('students.profile', $row['student']) }}">
                                            {{ $row['student']->name }}
                                        </a>
                                    </td>
                                    <td class="py-2">{{ $row['student']->username }}</td>
                                    <td class="py-2">
                                        <span class="badge {{ $statusStyles[$row['status']] ?? 'badge-neutral' }}">
                                            {{ __('ui.status.' . $row['status']) }}
                                        </span>
                                    </td>
                                    <td class="py-2">{{ $row['record']?->check_in_at?->format('H:i') ?? '-' }}</td>
                                    <td class="py-2">{{ $row['record']?->check_out_at?->format('H:i') ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php
        $chartLabels = [
            __('ui.status.present'),
            __('ui.status.late'),
            __('ui.status.absent'),
            __('ui.status.excused'),
            __('ui.status.sick'),
        ];
        $chartCounts = [
            $summary['present'],
            $summary['late'],
            $summary['absent'],
            $summary['excused'],
            $summary['sick'],
        ];
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('summaryChart').getContext('2d');
        const axisColor = '#94a3b8';
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: @json(__('ui.labels.count')),
                    data: @json($chartCounts),
                    backgroundColor: ['#16a34a', '#f59e0b', '#dc2626', '#2563eb', '#7c3aed'],
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        ticks: { color: axisColor },
                        grid: { color: 'rgba(148,163,184,0.2)' }
                    },
                    x: {
                        ticks: { color: axisColor },
                        grid: { color: 'rgba(148,163,184,0.1)' }
                    }
                }
            }
        });
    </script>
</x-app-layout>
