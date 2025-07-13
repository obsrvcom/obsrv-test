<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('devices/register', [\App\Http\Controllers\Api\V1\DeviceController::class, 'register']);
    Route::post('auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
    Route::get('check-subdomain', [\App\Http\Controllers\Api\V1\CompanyController::class, 'checkSubdomain']);
    // API v1 routes will go here
});
