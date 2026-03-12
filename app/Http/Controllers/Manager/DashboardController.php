<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * Displays a global search form, recent paid-manually orders,
     * recent warehouse orders, and order status counts.
     * The search box redirects to the appropriate controller
     * based on the query format (customer, order, tracking).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $query = $request->query('q');

        if (!empty($query)) {
            return $this->redirectSearch($query);
        }

        $paidManually = Order::with(['status', 'customer', 'total'])
            ->where('orders_status', 4)
            ->orderByDesc('date_purchased')
            ->limit(10)
            ->get();

        $inWarehouse = Order::with(['status', 'customer', 'total'])
            ->where('orders_status', 1)
            ->orderByDesc('date_purchased')
            ->limit(10)
            ->get();

        $orderStatuses = OrderStatus::all();

        return view('manager.dashboard', compact('paidManually', 'inWarehouse', 'orderStatuses'));
    }

    /**
     * Determine the model being searched and redirect to the
     * appropriate controller's search action.
     */
    protected function redirectSearch(string $query): RedirectResponse
    {
        $trackingPrefix = config('apobox.tracking.prefix', 'TRK');

        // If query starts with the tracking prefix, redirect to tracking search
        if (str_starts_with(strtoupper($query), strtoupper($trackingPrefix))) {
            $strippedQuery = substr($query, strlen($trackingPrefix));
            return redirect()->route(auth('admin')->user()->role . '.tracking.search', ['q' => $strippedQuery]);
        }

        // If query is purely numeric, likely an order ID
        if (ctype_digit($query)) {
            return redirect()->route(auth('admin')->user()->role . '.orders.search', ['q' => $query]);
        }

        // Default: customer search
        return redirect()->route(auth('admin')->user()->role . '.customers.search', ['q' => $query]);
    }
}
