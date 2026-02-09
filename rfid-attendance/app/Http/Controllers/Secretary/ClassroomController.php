<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\AttendanceRecord;
use App\Models\ClassroomMembership;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function show(Request $request)
    {
        $date = (string) $request->query('date', now()->toDateString());

        $secretaryMembership = ClassroomMembership::query()
            ->where('student_user_id', auth()->id())
            ->where('is_secretary', true)
            ->activeOn($date)
            ->orderByDesc('active_from')
            ->with('classroom')
            ->first();

        $isAssignedSecretary = (bool) $secretaryMembership;
        $isActiveSecretary = (bool) $secretaryMembership;

        if (! $secretaryMembership) {
            $secretaryMembership = ClassroomMembership::query()
                ->where('student_user_id', auth()->id())
                ->where('is_secretary', true)
                ->orderByDesc('active_from')
                ->with('classroom')
                ->first();
            $isAssignedSecretary = (bool) $secretaryMembership;
            $isActiveSecretary = false;
        }

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

        $checkedIn = $records->whereNotNull('check_in_at')->count();
        $checkedOut = $records->whereNotNull('check_out_at')->count();

        $pendingRequests = AbsenceRequest::query()
            ->where('classroom_id', $classroom->id)
            ->where('status', 'pending')
            ->count();

        return view('secretary.classroom', [
            'classroom' => $classroom,
            'date' => $date,
            'totalStudents' => $memberships->count(),
            'checkedIn' => $checkedIn,
            'notCheckedIn' => $memberships->count() - $checkedIn,
            'notCheckedOut' => max(0, $checkedIn - $checkedOut),
            'pendingRequests' => $pendingRequests,
            'isAssignedSecretary' => $isAssignedSecretary,
            'isActiveSecretary' => $isActiveSecretary,
        ]);
    }
}
