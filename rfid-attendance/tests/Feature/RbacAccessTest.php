<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    private function seedRoles(): void
    {
        foreach (['admin', 'teacher', 'secretary', 'student'] as $roleName) {
            Role::findOrCreate($roleName);
        }
    }

    public function test_admin_routes_are_forbidden_for_non_admin(): void
    {
        $this->seedRoles();

        $student = User::factory()->create()->assignRole('student');

        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_student_routes_are_forbidden_for_non_student(): void
    {
        $this->seedRoles();

        $teacher = User::factory()->create()->assignRole('teacher');

        $this->actingAs($teacher)->get('/me/attendance')->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $this->seedRoles();

        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}

