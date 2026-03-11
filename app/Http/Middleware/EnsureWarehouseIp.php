<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWarehouseIp
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = config('apobox.admin_allowed_ips', []);

        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            abort(403, 'Access denied. Your IP is not authorized.');
        }

        return $next($request);
    }
}
