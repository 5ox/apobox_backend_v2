<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * List the authenticated customer's orders.
     */
    public function index(): View
    {
        // TODO: Port from CakePHP
        return view('customer.orders.index');
    }

    /**
     * Show a single order belonging to the authenticated customer.
     */
    public function show(int $id): View
    {
        // TODO: Port from CakePHP
        return view('customer.orders.show', compact('id'));
    }

    /**
     * Show the manual payment form for an order.
     */
    public function payManually(int $id): View
    {
        // TODO: Port from CakePHP
        return view('customer.orders.pay', compact('id'));
    }

    /**
     * Process manual payment for an order.
     */
    public function processPayment(Request $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Process payment via gateway, update order status
        return redirect("/orders/{$id}");
    }
}
