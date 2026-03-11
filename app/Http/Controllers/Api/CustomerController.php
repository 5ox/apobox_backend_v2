<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Show a customer by ID.
     */
    public function show(int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Create a new customer via API.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([], 201);
    }

    /**
     * Authenticate a customer via API.
     */
    public function login(Request $request): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Send a notification to a customer.
     */
    public function notify(Request $request, int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Charge a customer's payment method.
     */
    public function charge(Request $request): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }
}
