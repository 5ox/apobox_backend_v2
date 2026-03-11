<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLayout
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth('admin')->user();
        $prefix = $request->segment(1); // 'manager' or 'employee'

        View::share('isManager', $admin && $admin->role === 'manager');
        View::share('isEmployee', $admin && $admin->role === 'employee');
        View::share('adminPrefix', $prefix);

        return $next($request);
    }
}
