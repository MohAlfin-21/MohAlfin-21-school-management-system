<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassroomController extends Controller
{
    public function index()
    {
        return view('admin.classrooms.index', [
            'classrooms' => Classroom::query()
                ->select('id', 'name', 'homeroom_teacher_id')
                ->with('homeroomTeacher:id,name')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.classrooms.create', [
            'teachers' => User::role('teacher')
                ->select('users.id', 'users.name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Classroom::class, 'name')],
            'grade' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $classroom = Classroom::query()->create($validated);

        return redirect()->route('admin.classrooms.show', $classroom)->with('status', 'ui.messages.classroom_created');
    }

    public function show(Classroom $classroom)
    {
        $memberships = ClassroomMembership::query()
            ->select('classroom_memberships.*')
            ->join('users', 'users.id', '=', 'classroom_memberships.student_user_id')
            ->where('classroom_id', $classroom->id)
            ->with('student:id,name,username')
            ->orderByDesc('is_secretary')
            ->orderBy('users.username')
            ->get();

        return view('admin.classrooms.show', [
            'classroom' => $classroom->load('homeroomTeacher'),
            'memberships' => $memberships,
            'students' => User::role('student')
                ->select('users.id', 'users.name', 'users.username')
                ->orderBy('username')
                ->get(),
            'teachers' => User::role('teacher')
                ->select('users.id', 'users.name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeMember(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'student_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'is_secretary' => ['nullable', 'boolean'],
        ]);

        $student = User::query()->findOrFail($validated['student_user_id']);
        if (! $student->hasRole('student')) {
            return back()->withErrors(['student_user_id' => 'User must have student role.']);
        }

        $isSecretary = (bool) ($validated['is_secretary'] ?? false);

        $exists = ClassroomMembership::query()
            ->where('classroom_id', $classroom->id)
            ->where('student_user_id', $student->id)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.classrooms.show', $classroom)
                ->withErrors(['student_user_id' => 'Student already added to this class.']);
        }

        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'is_secretary' => $isSecretary,
            'active_from' => now()->toDateString(),
        ]);

        if ($isSecretary && ! $student->hasRole('secretary')) {
            $student->assignRole('secretary');
        }

        return redirect()->route('admin.classrooms.show', $classroom)->with('status', 'ui.messages.member_added');
    }

    public function updateMember(Request $request, Classroom $classroom, ClassroomMembership $membership)
    {
        abort_unless($membership->classroom_id === $classroom->id, 404);

        $validated = $request->validate([
            'is_secretary' => ['nullable', 'boolean'],
        ]);

        $isSecretary = (bool) ($validated['is_secretary'] ?? false);

        $membership->forceFill([
            'is_secretary' => $isSecretary,
        ])->save();

        $student = $membership->student()->first();
        if ($student) {
            if ($isSecretary) {
                if (! $student->hasRole('secretary')) {
                    $student->assignRole('secretary');
                }
            } else {
                $stillSecretary = ClassroomMembership::query()
                    ->where('student_user_id', $student->id)
                    ->where('is_secretary', true)
                    ->exists();

                if (! $stillSecretary && $student->hasRole('secretary')) {
                    $student->removeRole('secretary');
                }
            }
        }

        return redirect()->route('admin.classrooms.show', $classroom)->with('status', 'ui.messages.member_updated');
    }

    public function destroyMember(Classroom $classroom, ClassroomMembership $membership)
    {
        abort_unless($membership->classroom_id === $classroom->id, 404);

        $student = $membership->student()->first();
        $membership->delete();

        if ($student && $student->hasRole('secretary')) {
            $stillSecretary = ClassroomMembership::query()
                ->where('student_user_id', $student->id)
                ->where('is_secretary', true)
                ->exists();

            if (! $stillSecretary) {
                $student->removeRole('secretary');
            }
        }

        return redirect()->route('admin.classrooms.show', $classroom)->with('status', 'ui.messages.member_deleted');
    }

    public function updateHomeroomTeacher(Request $request, Classroom $classroom)
    {
        $validated = $request->validate([
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $teacherId = $validated['homeroom_teacher_id'] ?? null;
        if ($teacherId) {
            $teacher = User::query()->findOrFail($teacherId);
            if (! $teacher->hasRole('teacher')) {
                return back()->withErrors(['homeroom_teacher_id' => 'User must have teacher role.']);
            }
        }

        $classroom->forceFill(['homeroom_teacher_id' => $teacherId])->save();

        return redirect()->route('admin.classrooms.show', $classroom)->with('status', 'ui.messages.classroom_updated');
    }
}
