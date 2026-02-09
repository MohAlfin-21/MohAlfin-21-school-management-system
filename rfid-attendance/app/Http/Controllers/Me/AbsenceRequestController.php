<?php

namespace App\Http\Controllers\Me;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\AbsenceRequestFile;
use App\Models\AttendanceSetting;
use App\Models\ClassroomMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AbsenceRequestController extends Controller
{
    public function create()
    {
        return view('me.absence-requests.create');
    }

    public function store(Request $request)
    {
        $settings = AttendanceSetting::current();

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'type' => ['required', 'string', Rule::in(['permission', 'sick', 'other'])],
            'reason_text' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:'.((int) $settings->max_upload_mb * 1024)],
        ]);

        $user = $request->user();
        $startDate = (string) $validated['start_date'];

        $membership = ClassroomMembership::query()
            ->where('student_user_id', $user->id)
            ->activeOn($startDate)
            ->orderByDesc('active_from')
            ->first();

        if (! $membership) {
            return back()->withErrors(['start_date' => 'Kamu belum terdaftar di kelas pada tanggal ini.'])->withInput();
        }

        $absenceRequest = AbsenceRequest::query()->create([
            'student_user_id' => $user->id,
            'classroom_id' => $membership->classroom_id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'type' => $validated['type'],
            'reason_text' => $validated['reason_text'] ?? null,
            'status' => 'pending',
        ]);

        $file = $validated['file'];
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $storedPath = Storage::disk('local')->putFileAs(
            "absence_requests/{$absenceRequest->id}",
            $file,
            Str::uuid()->toString().'.'.$extension
        );

        AbsenceRequestFile::query()->create([
            'absence_request_id' => $absenceRequest->id,
            'path' => $storedPath,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => $file->getSize(),
        ]);

        return redirect()->route('me.attendance.index')->with('status', 'ui.messages.absence_request_sent');
    }
}
