<?php

namespace Database\Seeders;

use App\Models\AttendanceSetting;
use App\Models\Classroom;
use App\Models\ClassroomMembership;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'absence_request_files',
            'absence_requests',
            'attendance_events',
            'attendance_records',
            'rfid_last_scans',
            'rfid_cards',
            'devices',
            'classroom_memberships',
            'classrooms',
            'student_profiles',
            'teacher_profiles',
            'sessions',
            'password_reset_tokens',
            'job_batches',
            'failed_jobs',
            'jobs',
            'cache_locks',
            'cache',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'roles',
            'permissions',
            'users',
            'attendance_settings',
        ] as $table) {
            DB::table($table)->delete();
        }
        Schema::enableForeignKeyConstraints();

        foreach (['admin', 'teacher', 'secretary', 'student'] as $roleName) {
            Role::findOrCreate($roleName);
        }

        AttendanceSetting::query()->create([
            'timezone' => 'Asia/Jakarta',
            'check_in_start' => '05:45',
            'check_in_end' => '07:10',
            'check_out_start' => '15:00',
            'check_out_end' => '16:45',
            'max_upload_mb' => 5,
            'allowed_mimes' => 'image/jpeg,image/png',
        ]);

        $admin = User::query()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@school.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->syncRoles(['admin']);

        $classroom = Classroom::query()->create([
            'name' => 'XI RPL 1',
            'grade' => 'XI',
            'major' => 'RPL',
        ]);

        // Anonymized demo names to avoid publishing real identities
        $students = [
            ['name' => 'Student 01', 'gender' => 'L'],
            ['name' => 'Student 02', 'gender' => 'L'],
            ['name' => 'Student 03', 'gender' => 'L'],
            ['name' => 'Student 04', 'gender' => 'P'],
            ['name' => 'Student 05', 'gender' => 'P'],
            ['name' => 'Student 06', 'gender' => 'L'],
            ['name' => 'Student 07', 'gender' => 'L'],
            ['name' => 'Student 08', 'gender' => 'P'],
            ['name' => 'Student 09', 'gender' => 'P'],
            ['name' => 'Student 10', 'gender' => 'P'],
            ['name' => 'Student 11', 'gender' => 'P'],
            ['name' => 'Student 12', 'gender' => 'L'],
            ['name' => 'Student 13', 'gender' => 'L'],
            ['name' => 'Student 14', 'gender' => 'P'],
            ['name' => 'Student 15', 'gender' => 'L'],
            ['name' => 'Student 16', 'gender' => 'L'],
            ['name' => 'Student 17', 'gender' => 'P'],
            ['name' => 'Student 18', 'gender' => 'L'],
            ['name' => 'Student 19', 'gender' => 'L'],
            ['name' => 'Student 20', 'gender' => 'P'],
            ['name' => 'Student 21', 'gender' => 'L'],
            ['name' => 'Student 22', 'gender' => 'L'],
            ['name' => 'Student 23', 'gender' => 'P'],
            ['name' => 'Student 24', 'gender' => 'L'],
            ['name' => 'Student 25', 'gender' => 'L'],
            ['name' => 'Student 26', 'gender' => 'L'],
            ['name' => 'Student 27', 'gender' => 'P'],
            ['name' => 'Student 28', 'gender' => 'L'],
            ['name' => 'Student 29', 'gender' => 'P'],
            ['name' => 'Student 30', 'gender' => 'L'],
            ['name' => 'Student 31', 'gender' => 'P'],
            ['name' => 'Student 32', 'gender' => 'P'],
            ['name' => 'Student 33', 'gender' => 'L'],
            ['name' => 'Student 34', 'gender' => 'L'],
            ['name' => 'Student 35', 'gender' => 'L'],
        ];

        foreach ($students as $index => $studentData) {
            $number = $index + 1;
            $username = 's'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);

            $student = User::query()->create([
                'name' => $studentData['name'],
                'username' => $username,
                'email' => "{$username}@school.test",
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $student->syncRoles(['student']);

            if ($number === 35) {
                $student->assignRole('secretary');
            }

            StudentProfile::query()->create([
                'user_id' => $student->id,
                'nisn' => null,
                'gender' => $studentData['gender'],
            ]);

            ClassroomMembership::query()->create([
                'classroom_id' => $classroom->id,
                'student_user_id' => $student->id,
                'is_secretary' => $number === 35,
                'active_from' => now()->toDateString(),
            ]);
        }

        $teacherSources = [
            ['code' => 71, 'short' => 'Teacher 71', 'full' => 'Teacher 71'],
            ['code' => 81, 'short' => 'Teacher 81', 'full' => 'Teacher 81'],
            ['code' => 14, 'short' => 'Teacher 14', 'full' => 'Teacher 14'],
            ['code' => 73, 'short' => 'Teacher 73', 'full' => 'Teacher 73'],
            ['code' => 45, 'short' => 'Teacher 45', 'full' => 'Teacher 45'],
            ['code' => 79, 'short' => 'Teacher 79', 'full' => 'Teacher 79'],
            ['code' => 85, 'short' => 'Teacher 85', 'full' => 'Teacher 85'],
            ['code' => 16, 'short' => 'Teacher 16', 'full' => 'Teacher 16'],
            ['code' => 83, 'short' => 'Teacher 83', 'full' => 'Teacher 83'],
            ['code' => 50, 'short' => 'Teacher 50', 'full' => 'Teacher 50'],
            ['code' => 62, 'short' => 'Teacher 62', 'full' => 'Teacher 62'],
            ['code' => 80, 'short' => 'Teacher 80', 'full' => 'Teacher 80'],
            ['code' => 54, 'short' => 'Teacher 54', 'full' => 'Teacher 54'],
        ];

        $teachersByUsername = [];

        foreach ($teacherSources as $teacherData) {
            $code = str_pad((string) $teacherData['code'], 3, '0', STR_PAD_LEFT);
            $username = "t{$code}";
            $displayName = $teacherData['full'] ?? $teacherData['short'];

            $teacher = User::query()->create([
                'name' => $displayName,
                'username' => $username,
                'email' => "{$username}@school.test",
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);

            $teacher->syncRoles(['teacher']);

            TeacherProfile::query()->create([
                'user_id' => $teacher->id,
                'full_name_with_title' => $displayName,
            ]);

            $teachersByUsername[$username] = $teacher;
        }

        if (isset($teachersByUsername['t014'])) {
            $classroom->update(['homeroom_teacher_id' => $teachersByUsername['t014']->id]);
        }
    }
}
