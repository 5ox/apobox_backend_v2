<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $admin = auth('admin')->user();

        if (!$admin || $admin->role !== $role) {
            abort(403, 'Unauthorized role.');
        }

        return $next($request);
    }
}
