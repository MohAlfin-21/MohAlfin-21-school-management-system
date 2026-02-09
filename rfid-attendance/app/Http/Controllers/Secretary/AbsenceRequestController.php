<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\AttendanceRecord;
use App\Models\ClassroomMembership;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AbsenceRequestController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $secretaryMembership = ClassroomMembership::query()
            ->where('student_user_id', auth()->id())
            ->where('is_secretary', true)
            ->activeOn($today)
            ->orderByDesc('active_from')
            ->first();

        abort_unless($secretaryMembership, 403);

        $classroomId = $secretaryMembership->classroom_id;

        return view('secretary.absence-requests', [
            'requests' => AbsenceRequest::query()
                ->where('classroom_id', $classroomId)
                ->where('status', 'pending')
                ->with([
                    'student:id,name,username',
                    'files:id,absence_request_id,original_name',
                ])
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function approve(Request $request, AbsenceRequest $absenceRequest)
    {
        $this->authorize('review', $absenceRequest);

        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($absenceRequest->status !== 'pending') {
            return back()->withErrors(['review_note' => 'Request already reviewed.']);
        }

        $status = $absenceRequest->type === 'sick' ? 'sick' : 'excused';

        $period = CarbonPeriod::create($absenceRequest->start_date, $absenceRequest->end_date);

        foreach ($period as $date) {
            AttendanceRecord::query()->updateOrCreate(
                ['date' => $date->toDateString(), 'student_user_id' => $absenceRequest->student_user_id],
                [
                    'classroom_id' => $absenceRequest->classroom_id,
                    'status' => $status,
                    'note' => $absenceRequest->reason_text,
                    'check_in_at' => null,
                    'check_out_at' => null,
                    'check_in_method' => null,
                    'check_out_method' => null,
                    'check_out_type' => null,
                    'early_checkout_reason' => null,
                    'updated_by' => auth()->id(),
                ]
            );
        }

        $absenceRequest->forceFill([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'] ?? null,
        ])->save();

        return back()->with('status', 'ui.messages.request_approved');
    }

    public function reject(Request $request, AbsenceRequest $absenceRequest)
    {
        $this->authorize('review', $absenceRequest);

        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:500'],
        ]);

        if ($absenceRequest->status !== 'pending') {
            return back()->withErrors(['review_note' => 'Request already reviewed.']);
        }

        $absenceRequest->forceFill([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'],
        ])->save();

        return back()->with('status', 'ui.messages.request_rejected');
    }
}
