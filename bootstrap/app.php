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
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            $surface = env('APP_SURFACE', 'all');

            if (!in_array($surface, ['all', 'customer', 'admin'], true)) {
                $surface = 'all';
            }

            Route::middleware('web')->group(base_path('routes/common_web.php'));

            if (in_array($surface, ['all', 'customer'], true)) {
                Route::middleware('web')->group(base_path('routes/customer_web.php'));
            }

            if (in_array($surface, ['all', 'admin'], true)) {
                Route::middleware('web')->group(base_path('routes/admin_web.php'));
                Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust Railway's reverse proxy so Laravel detects HTTPS, real IP, etc.
        $middleware->trustProxies(at: '*');

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
