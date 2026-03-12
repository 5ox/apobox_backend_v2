<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * Displays actionable orders grouped by status:
     * Paid, Awaiting Payment, Warehouse, and Problem.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $query = $request->query('q');

        if (!empty($query)) {
            return $this->redirectSearch($query);
        }

        $eagerLoad = ['status', 'customer', 'total'];

        $paid = Order::with($eagerLoad)
            ->where('orders_status', 4)
            ->orderByDesc('last_modified')
            ->limit(25)
            ->get();

        $awaitingPayment = Order::with($eagerLoad)
            ->where('orders_status', 2)
            ->orderByDesc('last_modified')
            ->limit(25)
            ->get();

        $inWarehouse = Order::with($eagerLoad)
            ->where('orders_status', 1)
            ->orderByDesc('last_modified')
            ->limit(25)
            ->get();

        $problem = Order::with($eagerLoad)
            ->where('orders_status', 6)
            ->orderByDesc('last_modified')
            ->limit(25)
            ->get();

        // Today's package counts per employee
        $todayStats = Order::select('creator_id', DB::raw('COUNT(*) as total'))
            ->whereDate('date_purchased', today())
            ->whereNotNull('creator_id')
            ->groupBy('creator_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $admin = Admin::find($row->creator_id);
                return [
                    'name'  => $admin ? explode('@', $admin->email)[0] : 'Unknown',
                    'email' => $admin->email ?? 'unknown',
                    'count' => $row->total,
                ];
            });

        $todayTotal = $todayStats->sum('count');

        return view('manager.dashboard', compact(
            'paid', 'awaitingPayment', 'inWarehouse', 'problem',
            'todayStats', 'todayTotal',
        ));
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
