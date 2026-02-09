<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassroomMembership;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $membership = ClassroomMembership::query()
            ->where('student_user_id', $user->id)
            ->activeOn($today)
            ->orderByDesc('active_from')
            ->with('classroom')
            ->first();

        $todayRecord = AttendanceRecord::query()
            ->forDate($today)
            ->where('student_user_id', $user->id)
            ->first();

        return view('me.attendance', [
            'classroom' => $membership?->classroom,
            'today' => $today,
            'todayRecord' => $todayRecord,
            'history' => AttendanceRecord::query()
                ->where('student_user_id', $user->id)
                ->orderByDesc('date')
                ->paginate(30),
        ]);
    }
}
