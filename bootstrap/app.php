<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'company.selected' => \App\Http\Middleware\EnsureCompanySelected::class,
            'company.admin' => \App\Http\Middleware\EnsureCompanyAdmin::class,
        ]);
        $middleware->api([\App\Http\Middleware\DeviceAndAuthHeaders::class]);
        $middleware->web([
            \App\Http\Middleware\HandleCompanySubdomain::class,
            \App\Http\Middleware\TrackWebBrowserDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
