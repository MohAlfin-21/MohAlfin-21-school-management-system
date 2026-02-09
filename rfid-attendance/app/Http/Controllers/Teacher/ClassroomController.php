<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        return view('teacher.classes.index', [
            'classrooms' => Classroom::query()
                ->select('id', 'name')
                ->where('homeroom_teacher_id', auth()->id())
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function show(Request $request, Classroom $classroom)
    {
        abort_unless($classroom->homeroom_teacher_id === auth()->id(), 403);

        $date = (string) $request->query('date', now()->toDateString());

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
                'membership' => $membership,
                'record' => $record,
                'status' => $record?->status ?? 'absent',
            ];
        });

        if (in_array($request->query('export'), ['csv', 'excel'], true)) {
            return response()->streamDownload(function () use ($rows, $date, $classroom) {
                $handle = fopen('php://output', 'w');
                $delimiter = ';';
                fprintf($handle, "\xEF\xBB\xBF");
                fputcsv($handle, [__('ui.classrooms.title'), $classroom->name], $delimiter);
                fputcsv($handle, [__('ui.labels.date'), $date], $delimiter);
                fputcsv($handle, [], $delimiter);
                fputcsv($handle, [
                    __('ui.labels.student'),
                    __('ui.labels.username'),
                    __('ui.labels.status'),
                    __('ui.labels.check_in'),
                    __('ui.labels.check_out'),
                ], $delimiter);

                foreach ($rows as $row) {
                    $record = $row['record'];
                    fputcsv($handle, [
                        $row['student']?->name,
                        $row['student']?->username,
                        $row['status'],
                        optional($record?->check_in_at)->format('H:i:s'),
                        optional($record?->check_out_at)->format('H:i:s'),
                    ], $delimiter);
                }

                fclose($handle);
            }, "attendance_{$classroom->id}_{$date}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $summary = [
            'present' => $rows->whereIn('status', ['present', 'late'])->count(),
            'late' => $rows->where('status', 'late')->count(),
            'absent' => $rows->where('status', 'absent')->count(),
            'excused' => $rows->where('status', 'excused')->count(),
            'sick' => $rows->where('status', 'sick')->count(),
        ];

        return view('teacher.classes.show', [
            'classroom' => $classroom,
            'date' => $date,
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }
}
