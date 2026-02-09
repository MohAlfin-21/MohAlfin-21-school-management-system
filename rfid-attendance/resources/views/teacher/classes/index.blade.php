<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.nav.my_classes') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="glass-card p-6">
                @if ($classrooms->isEmpty())
                    <div class="text-slate-400">{{ __('ui.teacher.no_classes') }}</div>
                @else
                    <ul class="list-disc pl-5 space-y-2">
                        @foreach ($classrooms as $classroom)
                            <li>
                                <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('teacher.classes.show', $classroom) }}">{{ $classroom->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
