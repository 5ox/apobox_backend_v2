<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Share current user with all views
        View::composer('*', function ($view) {
            $guard = null;
            if (auth('admin')->check()) {
                $guard = 'admin';
            } elseif (auth('customer')->check()) {
                $guard = 'customer';
            }

            $view->with('u', $guard ? auth($guard)->user() : null);
            $view->with('authGuard', $guard);
        });

        // Share admin layout vars
        View::composer('layouts.manager', function ($view) {
            $admin = auth('admin')->user();
            $view->with('isManager', $admin && $admin->role === 'manager');
            $view->with('isEmployee', $admin && $admin->role === 'employee');
        });
    }
}
