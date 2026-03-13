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

        // Employee activity stats
        $statsRange = $request->query('stats', '7d');
        $statsDays = match ($statsRange) {
            '30d'  => 30,
            '90d'  => 90,
            '12m'  => 365,
            default => 7,
        };
        $statsRange = in_array($statsRange, ['7d', '30d', '90d', '12m']) ? $statsRange : '7d';

        // Grouping: 7d = daily, 30d/90d = weekly, 12m = monthly
        $statsGrouping = match ($statsRange) {
            '30d', '90d' => 'week',
            '12m'        => 'month',
            default      => 'day',
        };

        $admins = Admin::whereIn('role', ['manager', 'employee'])->get()->keyBy('id');
        $rangeStart = today()->subDays($statsDays - 1);

        $dailyRows = Order::select(
                DB::raw('DATE(date_purchased) as day'),
                'creator_id',
                DB::raw('COUNT(*) as total')
            )
            ->whereDate('date_purchased', '>=', $rangeStart)
            ->whereNotNull('creator_id')
            ->groupBy('day', 'creator_id')
            ->orderBy('day')
            ->get();

        $employeeIds = $dailyRows->pluck('creator_id')->unique();
        $employeeNames = $employeeIds->mapWithKeys(function ($id) use ($admins) {
            $admin = $admins->get($id);
            return [$id => $admin ? explode('@', $admin->email)[0] : 'Unknown'];
        });

        // Build period stats based on grouping
        $periodStats = collect();

        if ($statsGrouping === 'day') {
            // Daily rows
            for ($i = $statsDays - 1; $i >= 0; $i--) {
                $date = today()->subDays($i);
                $dateStr = $date->toDateString();
                $dayCounts = $dailyRows->where('day', $dateStr);

                $byEmployee = $employeeIds->mapWithKeys(function ($id) use ($dayCounts) {
                    return [$id => (int) $dayCounts->where('creator_id', $id)->first()?->total];
                });

                $periodStats->push([
                    'date'       => $date,
                    'label'      => $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('D n/j')),
                    'total'      => $byEmployee->sum(),
                    'byEmployee' => $byEmployee,
                    'isCurrent'  => $date->isToday(),
                ]);
            }
        } elseif ($statsGrouping === 'week') {
            // Weekly rows — group daily data into ISO weeks
            $weekStart = $rangeStart->copy()->startOfWeek();
            while ($weekStart->lte(today())) {
                $weekEnd = $weekStart->copy()->endOfWeek();
                $weekDays = $dailyRows->filter(fn ($r) => $r->day >= $weekStart->toDateString() && $r->day <= $weekEnd->toDateString());

                $byEmployee = $employeeIds->mapWithKeys(function ($id) use ($weekDays) {
                    return [$id => (int) $weekDays->where('creator_id', $id)->sum('total')];
                });

                $isCurrent = today()->between($weekStart, $weekEnd);
                $label = $isCurrent ? 'This Week' : $weekStart->format('n/j') . '–' . $weekEnd->format('n/j');

                $periodStats->push([
                    'date'       => $weekStart->copy(),
                    'label'      => $label,
                    'total'      => $byEmployee->sum(),
                    'byEmployee' => $byEmployee,
                    'isCurrent'  => $isCurrent,
                ]);

                $weekStart->addWeek();
            }
        } else {
            // Monthly rows
            $monthStart = $rangeStart->copy()->startOfMonth();
            while ($monthStart->lte(today())) {
                $monthEnd = $monthStart->copy()->endOfMonth();
                $monthDays = $dailyRows->filter(fn ($r) => $r->day >= $monthStart->toDateString() && $r->day <= $monthEnd->toDateString());

                $byEmployee = $employeeIds->mapWithKeys(function ($id) use ($monthDays) {
                    return [$id => (int) $monthDays->where('creator_id', $id)->sum('total')];
                });

                $isCurrent = today()->month === $monthStart->month && today()->year === $monthStart->year;
                $label = $isCurrent ? 'This Month' : $monthStart->format('M Y');

                $periodStats->push([
                    'date'       => $monthStart->copy(),
                    'label'      => $label,
                    'total'      => $byEmployee->sum(),
                    'byEmployee' => $byEmployee,
                    'isCurrent'  => $isCurrent,
                ]);

                $monthStart->addMonth();
            }
        }

        // Per-employee totals for the period
        $employeeTotals = $employeeIds->mapWithKeys(function ($id) use ($periodStats) {
            return [$id => $periodStats->sum(fn ($d) => $d['byEmployee'][$id] ?? 0)];
        })->sortDesc();

        $statsTotal = $periodStats->sum('total');

        // Column header for the table
        $periodLabel = match ($statsGrouping) {
            'week'  => 'Week',
            'month' => 'Month',
            default => 'Day',
        };

        return view('manager.dashboard', compact(
            'paid', 'awaitingPayment', 'inWarehouse', 'problem',
            'periodStats', 'employeeNames', 'employeeTotals',
            'statsRange', 'statsTotal', 'periodLabel',
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
