<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureWarehouseIp;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SetAdminLayout;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Health check with DB check, no session/Redis middleware
            Route::middleware([])->group(function () {
                Route::get('/health', \App\Http\Controllers\HealthCheckController::class);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'warehouse.ip' => EnsureWarehouseIp::class,
            'role' => EnsureRole::class,
            'admin.layout' => SetAdminLayout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (\Throwable $e) {
            if (app()->bound('sentry') && app()->environment('production', 'staging')) {
                app('sentry')->captureException($e);
            }
        });
    })->create();
