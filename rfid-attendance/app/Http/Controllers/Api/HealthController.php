<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(Request $request)
    {
        $dbOk = true;

        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable $exception) {
            $dbOk = false;
        }

        $device = $request->attributes->get('device');
        $deviceId = (int) ($device?->id ?? 0);
        $captureMode = $deviceId > 0
            ? (bool) Cache::get("rfid.capture_mode.device.{$deviceId}", false)
            : false;

        return response()->json([
            'ok' => $dbOk,
            'app' => true,
            'db' => $dbOk,
            'time' => now()->toIso8601String(),
            'device_id' => $deviceId,
            'capture_mode' => $captureMode,
            'mode' => $captureMode ? 'peek' : 'scan',
        ]);
    }
}
