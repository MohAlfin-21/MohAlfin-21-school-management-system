<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }

        if ($user->hasRole('secretary')) {
            return redirect()->route('secretary.classroom.show');
        }

        if ($user->hasRole('student')) {
            return redirect()->route('me.attendance.index');
        }

        return redirect()->route('profile.edit');
    }
}

