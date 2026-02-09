<?php

namespace Tests\Feature;

use App\Models\AbsenceRequest;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecretaryScopeTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        foreach (['admin', 'teacher', 'secretary', 'student'] as $roleName) {
            Role::findOrCreate($roleName);
        }
    }

    public function test_secretary_cannot_approve_other_class_requests(): void
    {
        $this->seedRoles();

        $classA = Classroom::query()->create(['name' => 'Class A']);
        $classB = Classroom::query()->create(['name' => 'Class B']);

        $secretary = User::factory()->create();
        $secretary->assignRole(['student', 'secretary']);

        ClassroomMembership::query()->create([
            'classroom_id' => $classA->id,
            'student_user_id' => $secretary->id,
            'is_secretary' => true,
            'active_from' => now()->toDateString(),
        ]);

        $studentB = User::factory()->create();
        ClassroomMembership::query()->create([
            'classroom_id' => $classB->id,
            'student_user_id' => $studentB->id,
            'is_secretary' => false,
            'active_from' => now()->toDateString(),
        ]);

        $requestB = AbsenceRequest::query()->create([
            'student_user_id' => $studentB->id,
            'classroom_id' => $classB->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'type' => 'permission',
            'reason_text' => 'Test',
            'status' => 'pending',
        ]);

        $this->actingAs($secretary)
            ->post(route('secretary.absence-requests.approve', $requestB), ['review_note' => 'ok'])
            ->assertForbidden();
    }

    public function test_secretary_can_approve_own_class_requests(): void
    {
        $this->seedRoles();

        $classA = Classroom::query()->create(['name' => 'Class A']);

        $secretary = User::factory()->create();
        $secretary->assignRole(['student', 'secretary']);

        ClassroomMembership::query()->create([
            'classroom_id' => $classA->id,
            'student_user_id' => $secretary->id,
            'is_secretary' => true,
            'active_from' => now()->toDateString(),
        ]);

        $studentA = User::factory()->create();
        ClassroomMembership::query()->create([
            'classroom_id' => $classA->id,
            'student_user_id' => $studentA->id,
            'is_secretary' => false,
            'active_from' => now()->toDateString(),
        ]);

        $requestA = AbsenceRequest::query()->create([
            'student_user_id' => $studentA->id,
            'classroom_id' => $classA->id,
            'start_date' => '2026-02-03',
            'end_date' => '2026-02-04',
            'type' => 'permission',
            'reason_text' => 'Test',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($secretary)
            ->post(route('secretary.absence-requests.approve', $requestA), ['review_note' => 'ok']);

        $response->assertSessionHas('status')->assertRedirect();

        $this->assertSame('approved', $requestA->refresh()->status);
        $this->assertSame(2, AttendanceRecord::query()->where('student_user_id', $studentA->id)->count());
        $this->assertSame(
            2,
            AttendanceRecord::query()->where('student_user_id', $studentA->id)->where('status', 'excused')->count()
        );
    }
}

