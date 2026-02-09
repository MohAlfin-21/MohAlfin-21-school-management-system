<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Illuminate\Http\Request;

class AttendanceSettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.attendance', [
            'settings' => AttendanceSetting::current(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'timezone' => ['required', 'string', 'max:255'],
            'check_in_start' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
            'check_in_end' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
            'check_out_start' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
            'check_out_end' => ['required', 'regex:/^\\d{2}:\\d{2}$/'],
            'late_after' => ['nullable', 'regex:/^\\d{2}:\\d{2}$/'],
            'max_upload_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_mimes' => ['required', 'string', 'max:255'],
        ]);

        $settings = AttendanceSetting::current();
        $settings->forceFill($validated)->save();

        return redirect()->route('admin.settings.attendance.edit')->with('status', 'ui.messages.settings_updated');
    }
}
