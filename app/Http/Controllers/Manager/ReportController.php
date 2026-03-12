<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
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

        return view('manager.reports.index', compact('statuses'));
    }
}
