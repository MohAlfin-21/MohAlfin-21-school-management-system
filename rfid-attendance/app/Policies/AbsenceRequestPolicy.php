<?php

namespace App\Policies;

use App\Models\AbsenceRequest;
use App\Models\ClassroomMembership;
use App\Models\User;

class AbsenceRequestPolicy
{
    public function view(User $user, AbsenceRequest $absenceRequest): bool
    {
        if ($user->id === $absenceRequest->student_user_id) {
            return true;
        }

        if ($user->hasRole('secretary')) {
            return $this->isSecretaryForClassroom($user, $absenceRequest->classroom_id);
        }

        if ($user->hasRole('teacher')) {
            return $absenceRequest->classroom?->homeroom_teacher_id === $user->id;
        }

        return false;
    }

    public function review(User $user, AbsenceRequest $absenceRequest): bool
    {
        return $user->hasRole('secretary') && $this->isSecretaryForClassroom($user, $absenceRequest->classroom_id);
    }

    private function isSecretaryForClassroom(User $user, int $classroomId): bool
    {
        $today = now()->toDateString();

        return ClassroomMembership::query()
            ->where('student_user_id', $user->id)
            ->where('classroom_id', $classroomId)
            ->where('is_secretary', true)
            ->activeOn($today)
            ->exists();
    }
}
