<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // 'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'company.access' => \App\Http\Middleware\EnsureCompanyAccess::class,
            'site.access' => \App\Http\Middleware\EnsureSiteAccess::class,
            'company.admin' => \App\Http\Middleware\EnsureCompanyAdmin::class,
        ]);
        $middleware->api([\App\Http\Middleware\DeviceAndAuthHeaders::class]);
        $middleware->web([
            \App\Http\Middleware\DeviceApiKeyWebAuth::class,
            \App\Http\Middleware\TrackWebBrowserDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
