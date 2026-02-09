<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.nav.devices') }}
            </h2>
            <a href="{{ route('admin.devices.create') }}" class="btn-primary">
                {{ __('ui.devices.new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if ($createdToken)
                <div class="alert alert-warning relative" x-data="{ showToken: false, copied: false, copy() { navigator.clipboard.writeText('{{ $createdToken }}').then(() => { this.copied = true; setTimeout(() => this.copied = false, 1500); }); } }" x-cloak>
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold">{{ __('ui.labels.device_token') }}</div>
                            <div class="font-mono break-all px-3 py-2 mt-2 rounded bg-slate-900/60 border border-amber-300/30 flex items-center justify-between gap-3">
                                <span class="truncate">
                                    <span x-show="showToken" x-text="'{{ $createdToken }}'"></span>
                                    <span x-show="!showToken" class="select-none tracking-widest">************************</span>
                                </span>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-amber-200/40 bg-amber-200/10 text-amber-50 hover:bg-amber-200/20"
                                        @click="showToken = !showToken" :aria-label="showToken ? '{{ __('ui.actions.hide') }}' : '{{ __('ui.actions.show') }}'">
                                        <svg x-show="!showToken" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9.75a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                        </svg>
                                        <svg x-show="showToken" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 0 0 2.25 12s3.75 6.75 9.75 6.75a9.7 9.7 0 0 0 3.654-.684M6.228 6.228A9.705 9.705 0 0 1 12 5.25c6 0 9.75 6.75 9.75 6.75a10.49 10.49 0 0 1-2.178 2.727M6.228 6.228 3 3m3.228 3.228 3.62 3.62m4.772 4.772 3.123 3.123m-3.123-3.123-3.62-3.62m0 0a2.25 2.25 0 1 0 3.182-3.182" />
                                        </svg>
                                    </button>
                                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-amber-200/40 bg-amber-200/10 text-amber-50 hover:bg-amber-200/20"
                                        @click="copy()" aria-label="{{ __('ui.actions.copy') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 9.75A2.25 2.25 0 0 1 11.25 7.5h6A2.25 2.25 0 0 1 19.5 9.75v6A2.25 2.25 0 0 1 17.25 18h-6A2.25 2.25 0 0 1 9 15.75v-6Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 15.75h-1.5A2.25 2.25 0 0 1 3 13.5v-6A2.25 2.25 0 0 1 4.5 5.25h6A2.25 2.25 0 0 1 12.75 7.5v1.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-xs text-amber-100/80 mt-2">{{ __('ui.labels.device_token_keep_safe') }}</div>
                        </div>
                    </div>
                    <div x-show="copied" x-transition class="absolute right-4 -bottom-3 translate-y-full text-xs bg-amber-200/90 text-amber-900 px-3 py-1 rounded shadow">
                        {{ __('ui.labels.copy_success') }}
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    @php
                        $now = now();
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('ui.labels.no') }}</th>
                                    <th class="py-2">{{ __('ui.labels.name') }}</th>
                                    <th class="py-2">{{ __('ui.labels.location') }}</th>
                                    <th class="py-2">{{ __('ui.labels.active') }}</th>
                                    <th class="py-2">{{ __('ui.labels.connection') }}</th>
                                    <th class="py-2">{{ __('ui.labels.last_seen') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($devices as $device)
                                    @php
                                        $isConnected = $device->last_seen_at && $device->last_seen_at->diffInSeconds($now) <= 120;
                                    @endphp
                                    <tr>
                                        <td class="py-2 text-slate-400">{{ ($devices->currentPage() - 1) * $devices->perPage() + $loop->iteration }}</td>
                                        <td class="py-2">{{ $device->name }}</td>
                                        <td class="py-2">{{ $device->location ?? '-' }}</td>
                                        <td class="py-2">
                                            @if ($device->is_active)
                                                <span class="badge badge-success">{{ __('ui.status.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('ui.status.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2">
                                            @if ($device->last_seen_at)
                                                <span class="badge {{ $isConnected ? 'badge-success' : 'badge-neutral' }}">
                                                    {{ $isConnected ? __('ui.status.connected') : __('ui.status.offline') }}
                                                </span>
                                            @else
                                                <span class="badge badge-neutral">{{ __('ui.status.never') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2">{{ $device->last_seen_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                        <td class="py-2 text-right">
                                            <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('admin.devices.edit', $device) }}">{{ __('ui.actions.edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $devices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
