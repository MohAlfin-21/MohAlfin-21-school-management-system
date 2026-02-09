<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.classrooms.title') }}: {{ $classroom->name }}
            </h2>
            <a href="{{ route('admin.classrooms.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.back') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="alert alert-success">
                    {{ __(session('status')) }}
                </div>
            @endif

            <div class="glass-card p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <div class="text-slate-400 text-sm">{{ __('ui.classrooms.grade') }}</div>
                        <div class="font-medium">{{ $classroom->grade ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 text-sm">{{ __('ui.classrooms.major') }}</div>
                        <div class="font-medium">{{ $classroom->major ?? '-' }}</div>
                    </div>
                    <div class="md:col-span-1">
                        <div class="text-slate-400 text-sm">{{ __('ui.classrooms.homeroom_teacher') }}</div>
                        <div class="font-medium">{{ $classroom->homeroomTeacher?->name ?? '-' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.classrooms.homeroom.update', $classroom) }}" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="min-w-[240px]">
                        <x-input-label for="homeroom_teacher_id" :value="__('ui.classrooms.homeroom_teacher')" />
                        <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="input-base">
                            <option value="">{{ __('ui.actions.select') }}</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string) $classroom->homeroom_teacher_id === (string) $teacher->id)>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('homeroom_teacher_id')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ __('ui.actions.save') }}</x-primary-button>
                </form>
            </div>

            <div class="glass-card p-6 space-y-4">
                <div class="font-semibold">{{ __('ui.classrooms.add_student') }}</div>
                <form method="POST" action="{{ route('admin.classrooms.members.store', $classroom) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf

                    <div class="md:col-span-3">
                        <x-input-label for="student_user_id" :value="__('ui.labels.student')" />
                        <select id="student_user_id" name="student_user_id" class="input-base" required>
                            <option value="">{{ __('ui.actions.select') }}</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->username }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('student_user_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input id="is_secretary" name="is_secretary" value="1" type="checkbox" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60" />
                        <label for="is_secretary" class="text-sm text-slate-300">{{ __('ui.classrooms.secretary') }}</label>
                    </div>

                    <div class="md:col-span-4 flex justify-end">
                        <x-primary-button>{{ __('ui.actions.add') }}</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="glass-card p-6">
                <div class="font-semibold mb-4">{{ __('ui.classrooms.active_members') }}</div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th class="py-2">{{ __('ui.labels.no') }}</th>
                                <th class="py-2">{{ __('ui.labels.student') }}</th>
                                <th class="py-2">{{ __('ui.labels.username') }}</th>
                                <th class="py-2">{{ __('ui.classrooms.secretary') }}</th>
                                <th class="py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memberships as $membership)
                                <tr>
                                    <td class="py-2 text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="py-2">{{ $membership->student?->name }}</td>
                                    <td class="py-2">{{ $membership->student?->username }}</td>
                                    <td class="py-2">
                                        @if ($membership->is_secretary)
                                            <span class="badge badge-success">{{ __('ui.common.yes') }}</span>
                                        @else
                                            <span class="badge badge-neutral">{{ __('ui.common.no') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-right">
                                        <div class="inline-flex items-center gap-3">
                                            <form method="POST" action="{{ route('admin.classrooms.members.update', [$classroom, $membership]) }}" class="inline-flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <label class="inline-flex items-center gap-1">
                                                    <input type="checkbox" name="is_secretary" value="1" class="rounded border-slate-600 text-indigo-400 shadow-sm focus:ring-indigo-400 bg-slate-900/60" @checked($membership->is_secretary) />
                                                    <span class="text-xs text-slate-300">{{ __('ui.classrooms.secretary') }}</span>
                                                </label>
                                                <button class="text-indigo-300 hover:text-indigo-200 text-sm">{{ __('ui.actions.save') }}</button>
                                            </form>
                                            <button class="text-rose-300 hover:text-rose-200 text-sm" x-data @click.prevent="$dispatch('open-modal', 'delete-member-{{ $membership->id }}')">
                                                {{ __('ui.actions.delete') }}
                                            </button>
                                            <x-modal name="delete-member-{{ $membership->id }}" focusable maxWidth="sm">
                                                <div class="p-6 space-y-4 text-center">
                                                    <div class="text-lg font-semibold text-white">{{ __('ui.actions.delete') }}</div>
                                                    <p class="text-sm text-slate-300">
                                                        {{ __('ui.messages.member_delete_confirm') }}
                                                    </p>
                                                    <div class="flex items-center justify-center gap-2">
                                                        <button class="btn-secondary" x-on:click="$dispatch('close-modal', 'delete-member-{{ $membership->id }}')">
                                                            {{ __('ui.actions.cancel') }}
                                                        </button>
                                                        <form method="POST" action="{{ route('admin.classrooms.members.destroy', [$classroom, $membership]) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn-danger">{{ __('ui.actions.delete') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </x-modal>
                                        </div>
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
