<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\ClassroomMembership;
use App\Models\RfidCard;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class RfidScanController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:255'],
            'scanned_at' => ['nullable', 'date'],
        ]);

        $device = $request->attributes->get('device');
        $settings = AttendanceSetting::current();
        $timezone = $settings->timezone ?: config('app.timezone');

        $uid = RfidCard::normalizeUid($validated['uid']);

        $scannedAt = isset($validated['scanned_at'])
            ? CarbonImmutable::parse($validated['scanned_at'])->setTimezone($timezone)
            : now($timezone)->toImmutable();

        $date = $scannedAt->toDateString();

        $checkInStart = CarbonImmutable::parse("{$date} {$settings->check_in_start}", $timezone);
        $checkInEnd = CarbonImmutable::parse("{$date} {$settings->check_in_end}", $timezone);
        $checkOutStart = CarbonImmutable::parse("{$date} {$settings->check_out_start}", $timezone);
        $checkOutEnd = CarbonImmutable::parse("{$date} {$settings->check_out_end}", $timezone);

        $card = RfidCard::query()
            ->with('user:id,name')
            ->where('uid', $uid)
            ->where('status', 'active')
            ->first();

        if (! $card) {
            $this->logEvent($request, $device->id, $uid, null, $scannedAt, 'rejected', 'error', 'Kartu belum terdaftar', [
                'rule_hit' => 'card_not_registered',
            ]);

            return response()->json([
                'ok' => false,
                'action' => 'none',
                'code' => 'CARD_NOT_REGISTERED',
                'message' => 'Kartu belum terdaftar',
            ]);
        }

        $studentId = $card->user_id;

        $membership = ClassroomMembership::query()
            ->with('classroom:id,name')
            ->where('student_user_id', $studentId)
            ->activeOn($date)
            ->orderByDesc('active_from')
            ->first();

        if (! $membership) {
            $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'rejected', 'error', 'Siswa belum terdaftar di kelas', [
                'rule_hit' => 'no_classroom_membership',
            ]);

            return response()->json([
                'ok' => false,
                'action' => 'none',
                'code' => 'NO_CLASSROOM',
                'message' => 'Siswa belum terdaftar di kelas',
            ]);
        }

        $attendance = AttendanceRecord::query()
            ->forDate($date)
            ->where('student_user_id', $studentId)
            ->first();

        if ($scannedAt->betweenIncluded($checkInStart, $checkInEnd)) {
            if ($attendance && $attendance->status && in_array($attendance->status, ['excused', 'sick'], true)) {
                $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'rejected', 'info', 'Status izin/sakit, hubungi sekretaris', [
                    'rule_hit' => 'already_excused',
                ]);

                return response()->json([
                    'ok' => false,
                    'action' => 'none',
                    'code' => 'EXCUSED_DAY',
                    'message' => 'Status izin/sakit, hubungi sekretaris',
                ]);
            }

            if ($attendance && $attendance->check_in_at) {
                $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'check_in', 'info', 'Sudah check-in', [
                    'rule_hit' => 'already_checked_in',
                ]);

                return response()->json([
                    'ok' => true,
                    'action' => 'none',
                    'code' => 'ALREADY_CHECKED_IN',
                    'message' => 'Sudah check-in',
                ]);
            }

            $attendance = AttendanceRecord::query()->updateOrCreate(
                ['date' => $date, 'student_user_id' => $studentId],
                [
                    'classroom_id' => $membership->classroom_id,
                    'check_in_at' => $scannedAt,
                    'check_in_method' => 'rfid',
                    'status' => 'present',
                ]
            );

            $attendance->refresh();

            $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'check_in', 'success', 'Check-in berhasil', [
                'rule_hit' => 'check_in_window',
                'attendance_record_id' => $attendance->id,
            ]);

            return response()->json([
                'ok' => true,
                'action' => 'check_in',
                'code' => 'CHECKIN_OK',
                'message' => 'Check-in berhasil',
                'user' => [
                    'name' => $card->user?->name,
                    'classroom' => $membership->classroom?->name,
                ],
            ]);
        }

        if ($scannedAt->betweenIncluded($checkOutStart, $checkOutEnd)) {
            if (! $attendance || ! $attendance->check_in_at) {
                $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'check_out', 'error', 'Belum check-in', [
                    'rule_hit' => 'checkout_without_checkin',
                ]);

                return response()->json([
                    'ok' => false,
                    'action' => 'none',
                    'code' => 'NOT_CHECKED_IN',
                    'message' => 'Belum check-in',
                ]);
            }

            if ($attendance->check_out_at) {
                $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'check_out', 'info', 'Sudah check-out', [
                    'rule_hit' => 'already_checked_out',
                ]);

                return response()->json([
                    'ok' => true,
                    'action' => 'none',
                    'code' => 'ALREADY_CHECKED_OUT',
                    'message' => 'Sudah check-out',
                ]);
            }

            $attendance->forceFill([
                'check_out_at' => $scannedAt,
                'check_out_method' => 'rfid',
                'check_out_type' => 'normal',
            ])->save();

            $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'check_out', 'success', 'Check-out berhasil', [
                'rule_hit' => 'check_out_window',
                'attendance_record_id' => $attendance->id,
            ]);

            return response()->json([
                'ok' => true,
                'action' => 'check_out',
                'code' => 'CHECKOUT_OK',
                'message' => 'Check-out berhasil',
                'user' => [
                    'name' => $card->user?->name,
                    'classroom' => $membership->classroom?->name,
                ],
            ]);
        }

        $this->logEvent($request, $device->id, $uid, $studentId, $scannedAt, 'rejected', 'error', 'Di luar jam absensi', [
            'rule_hit' => 'outside_window',
        ]);

        return response()->json([
            'ok' => false,
            'action' => 'none',
            'code' => 'OUTSIDE_WINDOW',
            'message' => 'Di luar jam absensi',
        ]);
    }

    private function logEvent(Request $request, int $deviceId, string $uid, ?int $studentId, CarbonImmutable $scannedAt, string $action, string $result, string $message, array $meta = []): void
    {
        AttendanceEvent::query()->create([
            'device_id' => $deviceId,
            'uid' => $uid,
            'student_user_id' => $studentId,
            'scanned_at' => $scannedAt,
            'action' => $action,
            'result' => $result,
            'message' => $message,
            'meta' => [
                'ip' => $request->ip(),
                ...$meta,
            ],
        ]);
    }
}
