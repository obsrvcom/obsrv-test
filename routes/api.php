<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('device/register', [\App\Http\Controllers\Api\V1\DeviceController::class, 'register']);
    Route::middleware(\App\Http\Middleware\DeviceApiKeyAuth::class)->post('device/heartbeat', [\App\Http\Controllers\Api\V1\DeviceController::class, 'heartbeat']);
    Route::middleware(\App\Http\Middleware\DeviceApiKeyAuth::class)->get('device/fcm-token', [\App\Http\Controllers\Api\V1\DeviceController::class, 'getFcmToken']);
    Route::middleware(\App\Http\Middleware\DeviceApiKeyAuth::class)->post('device/refresh-fcm-token', [\App\Http\Controllers\Api\V1\DeviceController::class, 'refreshFcmToken']);
    Route::post('auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::middleware(\App\Http\Middleware\DeviceApiKeyAuth::class)->post('device/authenticate', [\App\Http\Controllers\Api\V1\DeviceController::class, 'authenticate']);
    Route::middleware('auth:sanctum')->group(function () {
        // API routes will go here
    });
    // API v1 routes will go here
    
    // Agent API routes
    Route::prefix('agents')->group(function () {
        Route::post('/register', [\App\Http\Controllers\Api\AgentController::class, 'register']);
        Route::post('/pairing-code', [\App\Http\Controllers\Api\AgentController::class, 'requestPairingCode']);
        Route::post('/heartbeat', [\App\Http\Controllers\Api\AgentController::class, 'heartbeat']);
        Route::post('/telegrams', [\App\Http\Controllers\Api\AgentController::class, 'telegrams']);
        Route::get('/{deviceId}/status', [\App\Http\Controllers\Api\AgentController::class, 'status']);
        
        // Internal route for pairing validation (requires auth)
        Route::middleware('auth:sanctum')->post('/validate-pairing', [\App\Http\Controllers\Api\AgentController::class, 'validatePairingCode']);
    });
});
