<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Role hierarchy levels (higher = more access).
     */
    protected const LEVELS = [
        'employee' => 1,
        'manager' => 2,
        'sysadmin' => 3,
    ];

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized role.');
        }

        $requiredLevel = self::LEVELS[$role] ?? 0;
        $adminLevel = self::LEVELS[$admin->role] ?? 0;

        if ($adminLevel < $requiredLevel) {
            abort(403, 'Unauthorized role.');
        }

        return $next($request);
    }
}
