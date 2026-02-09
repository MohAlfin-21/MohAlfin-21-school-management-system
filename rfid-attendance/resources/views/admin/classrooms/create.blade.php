<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-white">
            {{ __('ui.classrooms.new') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="glass-card p-6 space-y-6">
                <form method="POST" action="{{ route('admin.classrooms.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('ui.labels.name')" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="grade" :value="__('ui.classrooms.grade_optional')" />
                            <x-text-input id="grade" name="grade" class="block mt-1 w-full" :value="old('grade')" />
                            <x-input-error :messages="$errors->get('grade')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="major" :value="__('ui.classrooms.major_optional')" />
                            <x-text-input id="major" name="major" class="block mt-1 w-full" :value="old('major')" />
                            <x-input-error :messages="$errors->get('major')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="homeroom_teacher_id" :value="__('ui.classrooms.homeroom_teacher_optional')" />
                        <select id="homeroom_teacher_id" name="homeroom_teacher_id" class="input-base">
                            <option value="">-</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string) old('homeroom_teacher_id') === (string) $teacher->id)>
                                    {{ $teacher->name }} ({{ $teacher->username }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('homeroom_teacher_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.classrooms.index') }}" class="text-slate-400 hover:text-slate-200">{{ __('ui.actions.cancel') }}</a>
                        <x-primary-button>{{ __('ui.actions.create') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
