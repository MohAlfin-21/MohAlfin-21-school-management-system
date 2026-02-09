<?php

use App\Http\Controllers\Api\RfidPeekController;
use App\Http\Controllers\Api\RfidScanController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::post('/rfid/scan', RfidScanController::class)
    ->middleware(['device.token', 'throttle:rfid-scan']);

Route::post('/rfid/peek', RfidPeekController::class)
    ->middleware(['device.token', 'throttle:rfid-scan']);

Route::get('/health', HealthController::class)
    ->middleware(['device.token', 'throttle:60,1']);
