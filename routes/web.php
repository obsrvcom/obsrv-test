<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Main site routes (accessible on root domain)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Company selection routes (accessible to authenticated users, but don't require company selection)
Route::middleware(['auth', \App\Http\Middleware\HandleCompanySubdomain::class])->group(function () {
    Route::get('company/select', [\App\Http\Controllers\CompanyController::class, 'select'])->name('company.select');
    Route::post('company/switch', [\App\Http\Controllers\CompanyController::class, 'switch'])->name('company.switch');
    Route::get('company/create', [\App\Http\Controllers\CompanyController::class, 'create'])->name('company.create');
    Route::post('company/store', [\App\Http\Controllers\CompanyController::class, 'store'])->name('company.store');
});

// App routes (only accessible via subdomains and require company selection)
Route::middleware(['auth', 'company.selected', \App\Http\Middleware\HandleCompanySubdomain::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // User settings routes
    Route::redirect('user-settings', 'user-settings/profile');
    Volt::route('user-settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('user-settings/password', 'settings.password')->name('settings.password');
    Volt::route('user-settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('user-settings/devices', 'settings.devices')->name('settings.devices');

    // Company settings routes (require admin access)
    Route::middleware(['company.admin'])->group(function () {
        Route::redirect('company-settings', 'company-settings/general');
        Volt::route('company-settings/general', 'company-settings.general')->name('company.settings');
        Volt::route('company-settings/members', 'company-settings.members')->name('company.settings.members');
        Volt::route('company-settings/billing', 'company-settings.billing')->name('company.settings.billing');
    });
});

// API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api/user/devices', function () {
        return auth()->user()->devices;
    });
});

// Authentication routes (accessible on both main site and subdomains)
Route::get('magic-link/{token}', [\App\Http\Controllers\MagicLinkController::class, 'verify'])
    ->name('magic-link.verify');

Route::get('forgot-password/{token}', [\App\Http\Controllers\MagicLinkController::class, 'verifyForgotPassword'])
    ->name('forgot-password.verify');

require __DIR__.'/auth.php';
