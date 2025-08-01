<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Livewire\Volt\Volt;

// Broadcast auth routes
Broadcast::routes();
use App\Livewire\Site\Appointments;
use App\Livewire\Site\Quotations;
use App\Livewire\Site\Maintenance;
use App\Livewire\Site\Monitoring;
use App\Livewire\Site\Passwords;
use App\Livewire\Site\Internet;
use App\Livewire\Site\Agreement;
use App\Livewire\Site\Users;
use App\Livewire\Site\Settings;
use App\Livewire\Site\Tickets as SiteTickets;
use App\Livewire\Site\TicketView as SiteTicketView;
use App\Livewire\Company\Tickets as CompanyTickets;
use App\Livewire\Company\TicketView as CompanyTicketView;
use App\Livewire\Company\Users as CompanyUsers;
use App\Livewire\Company\Sites as CompanySites;
use App\Livewire\Company\SiteGroups as CompanySiteGroups;
use App\Livewire\Company\Contacts as CompanyContacts;
use App\Livewire\Company\ContactGroups as CompanyContactGroups;
use App\Livewire\Company\Issues as CompanyIssues;
use App\Models\Site;



// Main site routes (accessible on root domain)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// View selector route (for users with multiple access options)
Route::middleware(['auth'])->get('select', \App\Livewire\FullPageViewSelector::class)->name('view.select');

// Company selection routes (accessible to authenticated users, but don't require company selection)
Route::middleware(['auth'])->group(function () {
    Route::get('company/select', [\App\Http\Controllers\CompanyController::class, 'select'])->name('company.select');
    Route::post('company/switch', [\App\Http\Controllers\CompanyController::class, 'switch'])->name('company.switch');
    Route::get('company/create', [\App\Http\Controllers\CompanyController::class, 'create'])->name('company.create');
    Route::post('company/store', [\App\Http\Controllers\CompanyController::class, 'store'])->name('company.store');
});

// User settings
Route::middleware(['auth'])->group(function () {
    Route::view('user/settings', 'user.settings')->name('user.settings');
    Route::view('user/settings/profile', 'livewire.user.settings.profile')->name('settings.profile');
    Route::view('user/settings/password', 'livewire.user.settings.password')->name('settings.password');
    Route::view('user/settings/appearance', 'livewire.user.settings.appearance')->name('settings.appearance');
    Route::view('user/settings/devices', 'livewire.user.settings.devices')->name('settings.devices');
});

// App routes (now under /app/ and require authentication)
Route::bind('site', function ($value) {
    return Site::findOrFail($value);
});
Route::bind('company', function ($value) {
    return \App\Models\Company::findOrFail($value);
});
Route::prefix('app')->middleware(['auth'])->group(function () {
    // Main /app/ route - applies post-login routing logic
    Route::get('/', function () {
        try {
            return \App\Services\PostLoginRedirectService::redirectAfterLogin(auth()->user());
        } catch (\Exception $e) {
            \Log::error('Error in /app/ route redirect', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            // Fallback to view selector or company selection
            return redirect()->route('view.select');
        }
    })->name('app.index');
    // Company context routes
    Route::middleware(['auth', 'company.access'])->prefix('company/{company}')->name('company.')->group(function () {
        Route::redirect('/', '/app/company/{company}/dashboard'); // Redirect base to dashboard
        Volt::route('dashboard', 'app.company.dashboard')->name('dashboard');
        Route::get('tickets', CompanyTickets::class)->name('tickets');
        Route::get('tickets/{ticket}', CompanyTicketView::class)->name('tickets.view');
        Volt::route('agreements', 'app.company.agreements')->name('agreements');
        Volt::route('maintenance', 'app.company.maintenance')->name('maintenance');
        Volt::route('appointments', 'app.company.appointments')->name('appointments');
        Route::get('contacts', \App\Livewire\Company\Contacts::class)->name('contacts');
        Route::get('contact-groups', \App\Livewire\Company\ContactGroups::class)->name('contact-groups');
        Route::get('sites', \App\Livewire\Company\Sites::class)->name('sites');
        Route::get('site-groups', \App\Livewire\Company\SiteGroups::class)->name('site-groups');
        Volt::route('monitoring', 'app.company.monitoring')->name('monitoring');
        Route::get('teams', \App\Livewire\Company\Teams::class)->name('teams');
        Route::get('users', \App\Livewire\Company\Users::class)->name('users');
        Route::get('service', \App\Livewire\Company\ServicePlansNew::class)->name('service');
        Volt::route('billing', 'app.company.billing')->name('billing');
        // Company settings routes - require admin access for modifications
        Route::redirect('settings', 'settings/profile'); // Redirect to profile
        Volt::route('settings/profile', 'app.company.settings.profile')->middleware('company.admin')->name('settings.profile');
        Volt::route('settings/members', 'app.company.settings.members')->middleware('company.admin')->name('settings.members');
        Volt::route('settings/chats', 'app.company.settings.chats')->middleware('company.admin')->name('settings.chats');
        // Keep the old general route for backwards compatibility
        Volt::route('settings/general', 'app.company.settings.profile')->middleware('company.admin')->name('settings');
    });
    // Site context routes
    Route::prefix('site/{site}')->middleware(['site.access'])->group(function () {
        Route::redirect('/', '/app/site/{site}/dashboard'); // Redirect base to dashboard
        Route::view('dashboard', 'site.dashboard')->name('site.dashboard');
        Route::get('tickets', SiteTickets::class)->name('site.tickets');
        Route::get('tickets/{ticketId}', SiteTicketView::class)->name('site.tickets.view');
        Route::get('appointments', Appointments::class)->name('site.appointments');
        Route::get('quotations', Quotations::class)->name('site.quotations');
        Route::get('maintenance', Maintenance::class)->name('site.maintenance');
        Route::get('monitoring', Monitoring::class)->name('site.monitoring');
        Route::get('passwords', Passwords::class)->name('site.passwords');
        Route::get('internet', Internet::class)->name('site.internet');
        Route::get('agreement', Agreement::class)->name('site.agreement');
        Route::get('users', Users::class)->name('site.users');
        Route::get('settings', Settings::class)->name('site.settings');
    });
});

// Admin routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('admin.dashboard');
    // Add admin-specific routes here
});

// API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/api/user/devices', function () {
        return auth()->user()->devices;
    });
});

// Authentication routes at root
Route::get('magic-link/{token}', [\App\Http\Controllers\MagicLinkController::class, 'verify'])
    ->name('magic-link.verify');

Route::get('forgot-password/{token}', [\App\Http\Controllers\MagicLinkController::class, 'verifyForgotPassword'])
    ->name('forgot-password.verify');

require __DIR__.'/auth.php';
