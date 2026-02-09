<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.secretary.pending_requests') }}
            </h2>
            <a href="{{ route('secretary.classroom.show') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
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
                @if ($requests->isEmpty())
                    <div class="text-slate-400">{{ __('ui.secretary.no_pending_requests') }}</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('ui.labels.student') }}</th>
                                    <th class="py-2">{{ __('ui.labels.range') }}</th>
                                    <th class="py-2">{{ __('ui.labels.type') }}</th>
                                    <th class="py-2">{{ __('ui.labels.reason') }}</th>
                                    <th class="py-2">{{ __('ui.labels.files') }}</th>
                                    <th class="py-2">{{ __('ui.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $req)
                                    <tr class="align-top">
                                        <td class="py-2">
                                            <div class="font-medium">{{ $req->student?->name }}</div>
                                            <div class="text-xs text-slate-400">{{ $req->student?->username }}</div>
                                        </td>
                                        <td class="py-2">{{ $req->start_date->toDateString() }} - {{ $req->end_date->toDateString() }}</td>
                                        <td class="py-2">{{ __('ui.absence.type_' . $req->type) }}</td>
                                        <td class="py-2">{{ $req->reason_text ?? '-' }}</td>
                                        <td class="py-2">
                                            @foreach ($req->files as $file)
                                                <div>
                                                    <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('absence-request-files.download', $file) }}">
                                                        {{ $file->original_name }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="py-2">
                                            <div class="flex flex-col gap-2">
                                                <form method="POST" action="{{ route('secretary.absence-requests.approve', $req) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="review_note" class="input-compact" placeholder="{{ __('ui.labels.note_optional') }}" />
                                                    <button class="px-3 py-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 hover:bg-emerald-500/30 text-sm">{{ __('ui.actions.approve') }}</button>
                                                </form>
                                                <form method="POST" action="{{ route('secretary.absence-requests.reject', $req) }}" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="review_note" class="input-compact" placeholder="{{ __('ui.labels.note_required') }}" required />
                                                    <button class="px-3 py-2 rounded-lg bg-rose-500/20 border border-rose-500/30 text-rose-200 hover:bg-rose-500/30 text-sm">{{ __('ui.actions.reject') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
