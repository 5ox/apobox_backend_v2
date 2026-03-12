<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\CustomersInfo;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Show the reports dashboard.
     */
    public function index(Request $request): View
    {
        $statuses = Cache::remember('reports:statuses', 3600, fn () =>
            OrderStatus::pluck('orders_status_name', 'orders_status_id')->toArray()
        );

        // Employee activity: last 30 days, daily counts per employee
        $thirtyDaysAgo = today()->subDays(29);
        $admins = Admin::whereIn('role', ['manager', 'employee'])->get()->keyBy('id');

        $dailyRows = Order::select(
                DB::raw('DATE(date_purchased) as day'),
                'creator_id',
                DB::raw('COUNT(*) as total')
            )
            ->whereDate('date_purchased', '>=', $thirtyDaysAgo)
            ->whereNotNull('creator_id')
            ->groupBy('day', 'creator_id')
            ->orderBy('day')
            ->get();

        $employeeIds = $dailyRows->pluck('creator_id')->unique();
        $employeeNames = $employeeIds->mapWithKeys(function ($id) use ($admins) {
            $admin = $admins->get($id);
            return [$id => $admin ? explode('@', $admin->email)[0] : 'Unknown'];
        });

        $employeeActivity = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $dateStr = $date->toDateString();
            $dayCounts = $dailyRows->where('day', $dateStr);

            $byEmployee = $employeeIds->mapWithKeys(function ($id) use ($dayCounts) {
                return [$id => (int) $dayCounts->where('creator_id', $id)->first()?->total];
            });

            $employeeActivity->push([
                'date'       => $date,
                'label'      => $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('D n/j')),
                'total'      => $byEmployee->sum(),
                'byEmployee' => $byEmployee,
            ]);
        }

        // Per-employee totals for the 30-day period
        $employeeTotals = $employeeIds->mapWithKeys(function ($id) use ($employeeActivity) {
            return [$id => $employeeActivity->sum(fn ($d) => $d['byEmployee'][$id] ?? 0)];
        })->sortDesc();

        return view('manager.reports.index', compact(
            'statuses', 'employeeActivity', 'employeeNames', 'employeeTotals',
        ));
    }
}
