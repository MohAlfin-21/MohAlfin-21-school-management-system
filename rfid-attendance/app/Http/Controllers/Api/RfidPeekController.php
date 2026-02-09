<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\RfidCard;
use App\Models\RfidLastScan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class RfidPeekController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:255'],
            'scanned_at' => ['nullable', 'date'],
        ]);

        $device = $request->attributes->get('device');
        $settings = AttendanceSetting::current();
        $timezone = $settings->timezone ?: config('app.timezone');

        $uid = RfidCard::normalizeUid($validated['uid']);

        $scannedAt = isset($validated['scanned_at'])
            ? CarbonImmutable::parse($validated['scanned_at'])->setTimezone($timezone)
            : now($timezone)->toImmutable();

        RfidLastScan::query()->updateOrCreate(
            ['device_id' => $device->id],
            ['uid' => $uid, 'scanned_at' => $scannedAt]
        );

        $card = RfidCard::query()
            ->with('user:id,name,username')
            ->where('uid', $uid)
            ->first();

        return response()->json([
            'ok' => true,
            'code' => 'PEEK_OK',
            'uid' => $uid,
            'registered' => (bool) $card,
            'student' => $card ? [
                'id' => $card->user?->id,
                'name' => $card->user?->name,
                'username' => $card->user?->username,
            ] : null,
            'scanned_at' => $scannedAt->toIso8601String(),
        ]);
    }
}
