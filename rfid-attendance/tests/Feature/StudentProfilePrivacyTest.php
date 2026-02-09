<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentProfilePrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        foreach (['admin', 'teacher', 'secretary', 'student'] as $roleName) {
            Role::findOrCreate($roleName);
        }
    }

    public function test_student_cannot_view_other_student_profile(): void
    {
        $this->seedRoles();

        $classroom = Classroom::query()->create(['name' => 'XII IPA 1']);

        $student1 = User::factory()->create()->assignRole('student');
        $student2 = User::factory()->create()->assignRole('student');

        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student1->id,
            'active_from' => now()->toDateString(),
        ]);
        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student2->id,
            'active_from' => now()->toDateString(),
        ]);

        $this->actingAs($student1)->get(route('students.profile', $student2))->assertForbidden();
    }

    public function test_teacher_can_view_student_profile_in_homeroom_class(): void
    {
        $this->seedRoles();

        $teacher = User::factory()->create()->assignRole('teacher');
        $classroom = Classroom::query()->create(['name' => 'XII IPA 1', 'homeroom_teacher_id' => $teacher->id]);

        $student = User::factory()->create()->assignRole('student');
        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'active_from' => now()->toDateString(),
        ]);

        $this->actingAs($teacher)->get(route('students.profile', $student))->assertOk();
    }

    public function test_secretary_can_view_student_profile_in_own_class(): void
    {
        $this->seedRoles();

        $classroom = Classroom::query()->create(['name' => 'XII IPA 1']);

        $secretary = User::factory()->create()->assignRole(['student', 'secretary']);
        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $secretary->id,
            'is_secretary' => true,
            'active_from' => now()->toDateString(),
        ]);

        $student = User::factory()->create()->assignRole('student');
        ClassroomMembership::query()->create([
            'classroom_id' => $classroom->id,
            'student_user_id' => $student->id,
            'active_from' => now()->toDateString(),
        ]);

        $this->actingAs($secretary)->get(route('students.profile', $student))->assertOk();
    }

    public function test_student_can_view_own_profile(): void
    {
        $this->seedRoles();

        $student = User::factory()->create()->assignRole('student');

        $this->actingAs($student)->get(route('students.profile', $student))->assertOk();
    }
}

