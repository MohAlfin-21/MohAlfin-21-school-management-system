<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\User;
use App\Models\ClassroomMembership;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function show(Request $request, User $student)
    {
        $profile = StudentProfile::query()->firstOrNew(
            ['user_id' => $student->id],
            ['nisn' => $student->username]
        );

        $this->authorize('view', $profile);

        if (! $profile->exists) {
            $profile->save();
        }

        $today = now()->toDateString();
        $membership = ClassroomMembership::query()
            ->where('student_user_id', $student->id)
            ->activeOn($today)
            ->orderByDesc('active_from')
            ->with('classroom')
            ->first();

        return view('students.profile', [
            'student' => $student,
            'profile' => $profile,
            'classroom' => $membership?->classroom,
        ]);
    }
}
