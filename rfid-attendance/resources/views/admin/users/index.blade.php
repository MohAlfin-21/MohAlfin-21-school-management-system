<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.nav.users') }}
            </h2>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                {{ __('ui.users.new') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6">
                <form id="usersFilterForm" method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <x-input-label for="q" :value="__('ui.actions.search')" />
                        <x-text-input id="q" name="q" class="block mt-1" :value="$filters['q'] ?? ''" placeholder="{{ __('ui.actions.search') }}" />
                    </div>
                    <div>
                        <x-input-label for="role" :value="__('ui.labels.role')" />
                        <select id="role" name="role" class="input-base" onchange="document.getElementById('usersFilterForm').submit()">
                            <option value="">{{ __('ui.actions.select') }}</option>
                            @foreach ($roleOptions as $role)
                                <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="sort" :value="__('ui.labels.sort_by')" />
                        <select id="sort" name="sort" class="input-base" onchange="document.getElementById('usersFilterForm').submit()">
                            <option value="name" @selected(($filters['sort'] ?? '') === 'name')>{{ __('ui.labels.name') }}</option>
                            <option value="username" @selected(($filters['sort'] ?? '') === 'username')>{{ __('ui.labels.username') }}</option>
                            <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>{{ __('ui.labels.created_at') }}</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label for="direction" :value="__('ui.labels.direction')" />
                        <select id="direction" name="direction" class="input-base" onchange="document.getElementById('usersFilterForm').submit()">
                            <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>{{ __('ui.labels.asc') }}</option>
                            <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>{{ __('ui.labels.desc') }}</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <a class="btn-secondary" href="{{ route('admin.users.index') }}">{{ __('ui.actions.clear') }}</a>
                    </div>
                </form>
            </div>

            <script>
                (function () {
                    const input = document.getElementById('q');
                    const form = document.getElementById('usersFilterForm');
                    if (!input || !form) {
                        return;
                    }
                    let timer;
                    input.addEventListener('input', () => {
                        clearTimeout(timer);
                        timer = setTimeout(() => form.submit(), 350);
                    });
                })();
            </script>

            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('ui.labels.no') }}</th>
                                    <th class="py-2">{{ __('ui.labels.name') }}</th>
                                    <th class="py-2">{{ __('ui.labels.username') }}</th>
                                    <th class="py-2">{{ __('ui.labels.role') }}</th>
                                    <th class="py-2">{{ __('ui.labels.active') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rowNumber = ($users->currentPage() - 1) * $users->perPage();
                                @endphp
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="py-2 text-slate-400">{{ $rowNumber + $loop->iteration }}</td>
                                        <td class="py-2">{{ $user->name }}</td>
                                        <td class="py-2">{{ $user->username }}</td>
                                        <td class="py-2">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($user->roles as $role)
                                                    <span class="badge badge-neutral">{{ $role->name }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-2">
                                            @if ($user->is_active)
                                                <span class="badge badge-success">{{ __('ui.status.active') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('ui.status.inactive') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-right">
                                            <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('admin.users.edit', $user) }}">{{ __('ui.actions.edit') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
