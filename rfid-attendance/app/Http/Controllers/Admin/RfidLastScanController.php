<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\Device;
use App\Models\RfidCard;
use App\Models\RfidLastScan;
use Illuminate\Http\Request;

class RfidLastScanController extends Controller
{
    public function show(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'exists:devices,id'],
        ]);

        $device = Device::query()->findOrFail($validated['device_id']);

        $lastScan = RfidLastScan::query()
            ->where('device_id', $device->id)
            ->first();

        if (! $lastScan) {
            return response()->json([
                'ok' => true,
                'device_id' => $device->id,
                'uid' => null,
                'scanned_at' => null,
                'registered' => false,
                'student' => null,
            ]);
        }

        $card = RfidCard::query()
            ->with('user:id,name,username')
            ->where('uid', $lastScan->uid)
            ->first();
        $settings = AttendanceSetting::current();
        $timezone = $settings->timezone ?: config('app.timezone');
        $scannedAt = $lastScan->scanned_at?->setTimezone($timezone);

        return response()->json([
            'ok' => true,
            'device_id' => $device->id,
            'uid' => $lastScan->uid,
            'scanned_at' => $scannedAt?->toIso8601String(),
            'registered' => (bool) $card,
            'student' => $card ? [
                'id' => $card->user?->id,
                'name' => $card->user?->name,
                'username' => $card->user?->username,
            ] : null,
        ]);
    }

    public function clear(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'exists:devices,id'],
        ]);

        RfidLastScan::query()->where('device_id', $validated['device_id'])->delete();

        return response()->json([
            'ok' => true,
            'device_id' => (int) $validated['device_id'],
        ]);
    }
}
