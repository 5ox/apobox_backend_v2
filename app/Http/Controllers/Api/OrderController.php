<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\ChargeOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Show an order by ID.
     */
    public function show(int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Create a new order for a customer via API.
     */
    public function store(StoreOrderRequest $request, int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([], 201);
    }

    /**
     * Charge an order via API.
     */
    public function charge(ChargeOrderRequest $request, int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Change order status via API.
     */
    public function changeStatus(Request $request): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }
}
