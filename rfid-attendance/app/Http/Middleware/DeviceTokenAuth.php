<?php

namespace App\Http\Middleware;

use App\Models\AttendanceSetting;
use App\Models\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeviceTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->header('X-Device-Token', '');

        if ($token === '') {
            return response()->json([
                'ok' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'Missing device token',
            ], 401);
        }

        $tokenHash = hash('sha256', $token);

        $device = Device::query()
            ->where('token_hash', $tokenHash)
            ->where('is_active', true)
            ->first();

        if (! $device) {
            return response()->json([
                'ok' => false,
                'code' => 'UNAUTHORIZED',
                'message' => 'Invalid device token',
            ], 401);
        }

        $request->attributes->set('device', $device);

        $settings = AttendanceSetting::current();
        $timezone = $settings->timezone ?: config('app.timezone');
        $now = now($timezone);

        if (! $device->last_seen_at || $device->last_seen_at->diffInSeconds($now) >= 30) {
            $device->forceFill(['last_seen_at' => $now])->save();
        }

        return $next($request);
    }
}
