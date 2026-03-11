<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Show the reports index/dashboard with charts.
     */
    public function index(Request $request): View
    {
        $fromDate = now()->subMonths(7)->startOfMonth()->format('Y-m-d 00:00:00');
        $toDate = now()->endOfMonth()->format('Y-m-d 23:59:59');

        // Sales totals by month
        $salesChartData = Order::select(
                DB::raw("DATE_FORMAT(date_purchased, '%Y-%m') as period"),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN orders_status != 5 THEN 1 ELSE 0 END) as active_count')
            )
            ->whereBetween('date_purchased', [$fromDate, $toDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Customer signups by month
        $signupChartData = CustomersInfo::select(
                DB::raw("DATE_FORMAT(customers_info_date_account_created, '%Y-%m') as period"),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('customers_info_date_account_created', [$fromDate, $toDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Status counts for summary
        $statusCounts = Order::select('orders_status', DB::raw('COUNT(*) as count'))
            ->groupBy('orders_status')
            ->get()
            ->pluck('count', 'orders_status');

        return view('manager.reports.index', compact(
            'salesChartData',
            'signupChartData',
            'statusCounts'
        ));
    }
}
