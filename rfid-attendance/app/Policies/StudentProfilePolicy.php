<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    public function view(User $user, StudentProfile $studentProfile): bool
    {
        if ($user->id === $studentProfile->user_id) {
            return true;
        }

        $today = now()->toDateString();

        if ($user->hasRole('secretary')) {
            $secretaryClassroomIds = ClassroomMembership::query()
                ->where('student_user_id', $user->id)
                ->where('is_secretary', true)
                ->activeOn($today)
                ->pluck('classroom_id');

            if ($secretaryClassroomIds->isNotEmpty()) {
                return ClassroomMembership::query()
                    ->where('student_user_id', $studentProfile->user_id)
                    ->whereIn('classroom_id', $secretaryClassroomIds)
                    ->activeOn($today)
                    ->exists();
            }
        }

        if ($user->hasRole('teacher')) {
            $classroomIds = Classroom::query()
                ->where('homeroom_teacher_id', $user->id)
                ->pluck('id');

            if ($classroomIds->isNotEmpty()) {
                return ClassroomMembership::query()
                    ->where('student_user_id', $studentProfile->user_id)
                    ->whereIn('classroom_id', $classroomIds)
                    ->activeOn($today)
                    ->exists();
            }
        }

        return false;
    }

    public function update(User $user, StudentProfile $studentProfile): bool
    {
        return $user->id === $studentProfile->user_id;
    }
}
