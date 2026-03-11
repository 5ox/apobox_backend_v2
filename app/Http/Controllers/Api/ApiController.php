<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * API index/info endpoint.
     */
    public function index(): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([
            'name' => 'APO Box Account API',
            'version' => '2.0',
        ]);
    }

    /**
     * Generic not-implemented response.
     */
    public function notImplemented(): JsonResponse
    {
        return response()->json([
            'error' => 'Not implemented',
        ], 501);
    }
}
