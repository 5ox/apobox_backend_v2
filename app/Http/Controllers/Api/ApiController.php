<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * API health check / info endpoint.
     *
     * Returns API status, version info, and request diagnostics.
     * Ported from CakePHP ApisController::api_index().
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'response' => [
                'api-status' => 'OK',
            ],
            'request' => [
                'method' => $request->method(),
                'user-agent' => $request->header('User-Agent'),
                'content-type' => $request->header('Content-Type'),
                'authorization' => $request->header('Authorization') ? '***' : null,
                'remote-address' => $request->ip(),
                'accepts' => $request->getAcceptableContentTypes(),
            ],
            'name' => 'APO Box Account API',
            'version' => '2.0',
        ]);
    }

    /**
     * Generic not-implemented response for undefined API routes.
     *
     * Ported from CakePHP ApisController::api_not_implemented().
     */
    public function notImplemented(Request $request): JsonResponse
    {
        return response()->json([
            'error' => $request->method() . ' ' . $request->path() . ' is not a valid API endpoint.',
        ], 501);
    }
}
