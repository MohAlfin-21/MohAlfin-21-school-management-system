<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Device;
use App\Models\RfidCard;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'counts' => [
                'students' => User::role('student')->count(),
                'teachers' => User::role('teacher')->count(),
                'classrooms' => Classroom::query()->count(),
            ],
        ]);
    }
}
