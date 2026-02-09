<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
                {{ __('ui.nav.classrooms') }}
            </h2>
            <a href="{{ route('admin.classrooms.create') }}" class="btn-primary">
                {{ __('ui.classrooms.new') }}
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

            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th class="py-2">{{ __('ui.labels.name') }}</th>
                                    <th class="py-2">{{ __('ui.classrooms.homeroom_teacher') }}</th>
                                    <th class="py-2"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classrooms as $classroom)
                                    <tr>
                                        <td class="py-2">{{ $classroom->name }}</td>
                                        <td class="py-2">{{ $classroom->homeroomTeacher?->name ?? '-' }}</td>
                                        <td class="py-2 text-right">
                                            <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('admin.classrooms.show', $classroom) }}">{{ __('ui.actions.open') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $classrooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
