<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RfidLiveCaptureController extends Controller
{
    private function cacheKey(int $deviceId): string
    {
        return "rfid.capture_mode.device.{$deviceId}";
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'exists:devices,id'],
        ]);

        $device = Device::query()->findOrFail((int) $validated['device_id']);

        if (! $device->is_active) {
            return response()->json([
                'ok' => false,
                'message' => 'Device is inactive.',
            ], 422);
        }

        Cache::put($this->cacheKey($device->id), true, now()->addMinutes(10));

        return response()->json([
            'ok' => true,
            'device_id' => $device->id,
            'capture_mode' => true,
        ]);
    }

    public function stop(Request $request)
    {
        $validated = $request->validate([
            'device_id' => ['required', 'integer', 'exists:devices,id'],
        ]);

        $deviceId = (int) $validated['device_id'];

        Cache::forget($this->cacheKey($deviceId));

        return response()->json([
            'ok' => true,
            'device_id' => $deviceId,
            'capture_mode' => false,
        ]);
    }
}

