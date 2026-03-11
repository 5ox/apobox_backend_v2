<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    /**
     * Store a new address via API.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([], 201);
    }
}
