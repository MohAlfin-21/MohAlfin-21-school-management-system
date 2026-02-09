<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade')->nullable();
            $table->string('major')->nullable();
            $table->foreignId('homeroom_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name']);
        });

        Schema::create('classroom_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_secretary')->default(false);
            $table->date('active_from');
            $table->date('active_to')->nullable();
            $table->timestamps();

            $table->index(['student_user_id', 'is_secretary']);
            $table->index(['classroom_id', 'active_to']);
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('token_hash', 64)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('check_in_start', 5)->default('05:45');
            $table->string('check_in_end', 5)->default('07:10');
            $table->string('check_out_start', 5)->default('15:00');
            $table->string('check_out_end', 5)->default('16:45');
            $table->string('late_after', 5)->nullable();
            $table->unsignedInteger('max_upload_mb')->default(5);
            $table->string('allowed_mimes')->default('image/jpeg,image/png');
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('nisn')->unique()->nullable();
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('parent_phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->foreignId('user_id')->primary()->constrained('users')->cascadeOnDelete();
            $table->string('nip')->unique()->nullable();
            $table->string('full_name_with_title')->nullable();
            $table->string('phone_wa')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('public_bio')->nullable();
            $table->text('subjects_text')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->dateTime('check_in_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->string('check_in_method')->nullable();
            $table->string('check_out_method')->nullable();
            $table->string('check_out_type')->nullable();
            $table->string('status')->nullable();
            $table->text('note')->nullable();
            $table->text('early_checkout_reason')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'student_user_id']);
            $table->index(['classroom_id', 'date']);
        });

        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('uid');
            $table->foreignId('student_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scanned_at');
            $table->string('action');
            $table->string('result');
            $table->string('message');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_user_id', 'scanned_at']);
            $table->index(['device_id', 'scanned_at']);
        });

        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type');
            $table->text('reason_text')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->index(['classroom_id', 'status']);
            $table->index(['student_user_id', 'status']);
        });

        Schema::create('absence_request_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absence_request_id')->constrained('absence_requests')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_request_files');
        Schema::dropIfExists('absence_requests');
        Schema::dropIfExists('attendance_events');
        Schema::dropIfExists('attendance_records');
        Schema::dropIfExists('teacher_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('attendance_settings');
        Schema::dropIfExists('rfid_cards');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('classroom_memberships');
        Schema::dropIfExists('classrooms');
    }
};

