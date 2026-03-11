<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\ChargeOrderRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /**
     * Search and list orders.
     */
    public function search(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.orders.search');
    }

    /**
     * View a single order.
     */
    public function view(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.orders.view', compact('id'));
    }

    /**
     * Show the add order form for a customer.
     */
    public function add(int $customerId): View
    {
        // TODO: Port from CakePHP
        return view('manager.orders.add', compact('customerId'));
    }

    /**
     * Store a new order for a customer.
     */
    public function store(StoreOrderRequest $request, int $customerId): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Charge payment for an order.
     */
    public function charge(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Mark an order as shipped.
     */
    public function markAsShipped(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete an order.
     */
    public function deleteOrder(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show order status totals.
     */
    public function statusTotals(): View
    {
        // TODO: Port from CakePHP
        return view('manager.orders.status-totals');
    }

    /**
     * Print shipping label for an order.
     */
    public function printLabel(int $id): Response|RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Print FedEx label for an order.
     */
    public function printFedex(int $id, ?string $reprint = null): Response|RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete a shipping label for an order.
     */
    public function deleteLabel(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the orders report.
     */
    public function report(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.orders.report');
    }
}
