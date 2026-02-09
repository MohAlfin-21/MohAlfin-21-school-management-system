<?php

use App\Http\Controllers\Admin\AttendanceSettingController as AdminAttendanceSettingController;
use App\Http\Controllers\Admin\ClassroomController as AdminClassroomController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeviceController as AdminDeviceController;
use App\Http\Controllers\Admin\RfidCardController as AdminRfidCardController;
use App\Http\Controllers\Admin\RfidLastScanController as AdminRfidLastScanController;
use App\Http\Controllers\Admin\RfidLiveCaptureController as AdminRfidLiveCaptureController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AbsenceRequestFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Me\AbsenceRequestController as MeAbsenceRequestController;
use App\Http\Controllers\Me\AttendanceController as MeAttendanceController;
use App\Http\Controllers\Me\ProfileController as MeProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Secretary\AbsenceRequestController as SecretaryAbsenceRequestController;
use App\Http\Controllers\Secretary\AttendanceController as SecretaryAttendanceController;
use App\Http\Controllers\Secretary\ClassroomController as SecretaryClassroomController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\Teacher\ClassroomController as TeacherClassroomController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::post('/locale', LocaleController::class)->name('locale.switch');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/students/{student}/profile', [StudentProfileController::class, 'show'])->name('students.profile');
    Route::get('/absence-request-files/{file}', [AbsenceRequestFileController::class, 'download'])->name('absence-request-files.download');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('classrooms', AdminClassroomController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('classrooms/{classroom}/homeroom-teacher', [AdminClassroomController::class, 'updateHomeroomTeacher'])->name('classrooms.homeroom.update');
    Route::post('classrooms/{classroom}/members', [AdminClassroomController::class, 'storeMember'])->name('classrooms.members.store');
    Route::patch('classrooms/{classroom}/members/{membership}', [AdminClassroomController::class, 'updateMember'])->name('classrooms.members.update');
    Route::delete('classrooms/{classroom}/members/{membership}', [AdminClassroomController::class, 'destroyMember'])->name('classrooms.members.destroy');
    Route::resource('rfid-cards', AdminRfidCardController::class)->except(['show']);
    Route::get('rfid/last-scan', [AdminRfidLastScanController::class, 'show'])->name('rfid.last-scan');
    Route::post('rfid/last-scan/clear', [AdminRfidLastScanController::class, 'clear'])->name('rfid.last-scan.clear');
    Route::post('rfid/live-capture/start', [AdminRfidLiveCaptureController::class, 'start'])->name('rfid.live-capture.start');
    Route::post('rfid/live-capture/stop', [AdminRfidLiveCaptureController::class, 'stop'])->name('rfid.live-capture.stop');
    Route::resource('devices', AdminDeviceController::class)->except(['show']);
    Route::post('devices/{device}/regenerate-token', [AdminDeviceController::class, 'regenerateToken'])->name('devices.regenerate-token');
    Route::get('settings/attendance', [AdminAttendanceSettingController::class, 'edit'])->name('settings.attendance.edit');
    Route::put('settings/attendance', [AdminAttendanceSettingController::class, 'update'])->name('settings.attendance.update');
});

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('classes', [TeacherClassroomController::class, 'index'])->name('classes.index');
    Route::get('classes/{classroom}', [TeacherClassroomController::class, 'show'])->name('classes.show');
});

Route::middleware(['auth', 'role:secretary'])->prefix('secretary')->name('secretary.')->group(function () {
    Route::get('classroom', [SecretaryClassroomController::class, 'show'])->name('classroom.show');
    Route::get('attendance', [SecretaryAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance/{student}/mark', [SecretaryAttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('attendance/{student}/early-checkout', [SecretaryAttendanceController::class, 'earlyCheckout'])->name('attendance.early-checkout');
    Route::get('absence-requests', [SecretaryAbsenceRequestController::class, 'index'])->name('absence-requests.index');
    Route::post('absence-requests/{absenceRequest}/approve', [SecretaryAbsenceRequestController::class, 'approve'])->name('absence-requests.approve');
    Route::post('absence-requests/{absenceRequest}/reject', [SecretaryAbsenceRequestController::class, 'reject'])->name('absence-requests.reject');
});

Route::middleware(['auth', 'role:student'])->prefix('me')->name('me.')->group(function () {
    Route::get('attendance', [MeAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('absence-requests/create', [MeAbsenceRequestController::class, 'create'])->name('absence-requests.create');
    Route::post('absence-requests', [MeAbsenceRequestController::class, 'store'])->name('absence-requests.store');
    Route::get('profile', [MeProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [MeProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
