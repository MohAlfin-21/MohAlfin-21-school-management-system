<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassroomMembership;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = (string) $request->query('date', now()->toDateString());

        $secretaryMembership = ClassroomMembership::query()
            ->where('student_user_id', auth()->id())
            ->where('is_secretary', true)
            ->activeOn($date)
            ->orderByDesc('active_from')
            ->with('classroom')
            ->first();

        abort_unless($secretaryMembership, 403, 'Not assigned as secretary.');

        $classroom = $secretaryMembership->classroom;

        $memberships = $classroom->activeMemberships($date)
            ->with('student:id,name,username')
            ->get();

        $studentIds = $memberships->pluck('student_user_id')->all();

        $records = AttendanceRecord::query()
            ->forDate($date)
            ->whereIn('student_user_id', $studentIds)
            ->get()
            ->keyBy('student_user_id');

        $rows = $memberships->map(function ($membership) use ($records) {
            $record = $records->get($membership->student_user_id);

            return [
                'student' => $membership->student,
                'record' => $record,
                'status' => $record?->status ?? 'absent',
            ];
        });

        return view('secretary.attendance', [
            'classroom' => $classroom,
            'date' => $date,
            'rows' => $rows,
        ]);
    }

    public function mark(Request $request, User $student)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['present', 'late', 'absent', 'excused', 'sick'])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $date = (string) $validated['date'];

        $secretaryMembership = ClassroomMembership::query()
            ->where('student_user_id', auth()->id())
            ->where('is_secretary', true)
            ->activeOn($date)
            ->orderByDesc('active_from')
            ->first();

        abort_unless($secretaryMembership, 403);

        $classroomId = $secretaryMembership->classroom_id;

        $isStudentInClass = ClassroomMembership::query()
            ->where('student_user_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->activeOn($date)
            ->exists();

        abort_unless($isStudentInClass, 403);

        $record = AttendanceRecord::query()->firstOrNew([
            'date' => $date,
            'student_user_id' => $student->id,
        ]);

        $record->classroom_id = $classroomId;
        $record->status = $validated['status'];
        $record->note = $validated['note'] ?? null;
        $record->updated_by = auth()->id();

        if (in_array($record->status, ['present', 'late'], true)) {
            if (! $record->check_in_at) {
                $record->check_in_at = now();
                $record->check_in_method = 'manual';
            }
        }

        if (in_array($record->status, ['absent', 'excused', 'sick'], true)) {
            $record->check_in_at = null;
            $record->check_out_at = null;
            $record->check_in_method = null;
            $record->check_out_method = null;
            $record->check_out_type = null;
            $record->early_checkout_reason = null;
        }

        $record->save();

        return back()->with('status', 'ui.messages.attendance_updated');
    }

    public function earlyCheckout(Request $request, User $student)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $date = (string) $validated['date'];

        $secretaryMembership = ClassroomMembership::query()
            ->where('student_user_id', auth()->id())
            ->where('is_secretary', true)
            ->activeOn($date)
            ->orderByDesc('active_from')
            ->first();

        abort_unless($secretaryMembership, 403);

        $classroomId = $secretaryMembership->classroom_id;

        $isStudentInClass = ClassroomMembership::query()
            ->where('student_user_id', $student->id)
            ->where('classroom_id', $classroomId)
            ->activeOn($date)
            ->exists();

        abort_unless($isStudentInClass, 403);

        $record = AttendanceRecord::query()
            ->forDate($date)
            ->where('student_user_id', $student->id)
            ->first();

        if (! $record || ! $record->check_in_at || $record->check_out_at) {
            return back()->withErrors(['reason' => 'Student must be checked-in and not checked-out.']);
        }

        $record->forceFill([
            'check_out_at' => now(),
            'check_out_method' => 'manual',
            'check_out_type' => 'early',
            'early_checkout_reason' => $validated['reason'],
            'updated_by' => auth()->id(),
        ])->save();

        return back()->with('status', 'ui.messages.early_checkout_recorded');
    }
}
