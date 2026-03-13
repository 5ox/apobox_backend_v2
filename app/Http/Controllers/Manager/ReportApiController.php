<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomersInfo;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportApiController extends Controller
{
    /**
     * GET /reports/api/summary?range=30d|90d|12m
     *
     * Pre-computed KPI cards: total orders, active customers, size breakdown, avg per customer.
     */
    public function summary(Request $request): JsonResponse
    {
        $range = $request->input('range', '30d');
        $cacheKey = "reports:summary:{$range}";

        $data = Cache::remember($cacheKey, 1800, function () use ($range) {
            [$from, $to, $prevFrom, $prevTo] = $this->rangeToDates($range);

            // Current period totals
            $currentOrders = Order::whereBetween('date_purchased', [$from, $to])->count();
            $prevOrders = Order::whereBetween('date_purchased', [$prevFrom, $prevTo])->count();

            // Active customers (distinct senders with orders in period)
            $activeCustomers = Order::whereBetween('date_purchased', [$from, $to])
                ->distinct('customers_id')
                ->count('customers_id');

            // Package type breakdown
            $sizeBreakdown = Order::whereBetween('date_purchased', [$from, $to])
                ->select('package_type', DB::raw('COUNT(*) as count'))
                ->groupBy('package_type')
                ->orderByDesc('count')
                ->get()
                ->map(fn ($row) => [
                    'type' => $row->package_type ?: 'Unknown',
                    'count' => $row->count,
                ]);

            $totalForPercent = $sizeBreakdown->sum('count') ?: 1;
            $sizeBreakdown = $sizeBreakdown->map(fn ($row) => [
                ...$row,
                'percent' => round($row['count'] / $totalForPercent * 100, 1),
            ]);

            // Status breakdown (scoped to selected period)
            $statusCounts = Order::select('orders_status', DB::raw('COUNT(*) as count'))
                ->whereBetween('date_purchased', [$from, $to])
                ->groupBy('orders_status')
                ->get()
                ->pluck('count', 'orders_status');

            // Top 10 customers by volume this period
            $topCustomers = Order::whereBetween('date_purchased', [$from, $to])
                ->select('customers_id', 'customers_name', DB::raw('COUNT(*) as order_count'))
                ->groupBy('customers_id', 'customers_name')
                ->orderByDesc('order_count')
                ->limit(10)
                ->get();

            // Total customers
            $totalCustomers = Customer::where('is_active', true)->count();

            // Lifetime total orders (all time)
            $lifetimeShipped = Order::count();

            // Avg packages per customer
            $avgPerCustomer = $activeCustomers > 0
                ? round($currentOrders / $activeCustomers, 1)
                : 0;

            // Percent change
            $percentChange = $prevOrders > 0
                ? round(($currentOrders - $prevOrders) / $prevOrders * 100, 1)
                : ($currentOrders > 0 ? 100 : 0);

            return [
                'totalOrders' => $currentOrders,
                'prevOrders' => $prevOrders,
                'percentChange' => $percentChange,
                'activeCustomers' => $activeCustomers,
                'totalCustomers' => $totalCustomers,
                'avgPerCustomer' => $avgPerCustomer,
                'sizeBreakdown' => $sizeBreakdown,
                'statusCounts' => $statusCounts,
                'topCustomers' => $topCustomers,
                'lifetimeShipped' => $lifetimeShipped,
            ];
        });

        return response()->json($data);
    }

    /**
     * GET /reports/api/trends?metric=orders&interval=week&from=...&to=...
     *
     * Time-series data for charts.
     */
    public function trends(Request $request): JsonResponse
    {
        $interval = $request->input('interval', 'week');
        $from = $request->input('from', now()->subYear()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $metric = $request->input('metric', 'orders');

        $cacheKey = "reports:trends:{$metric}:{$interval}:{$from}:{$to}";

        $data = Cache::remember($cacheKey, 3600, function () use ($interval, $from, $to, $metric) {
            $groupFormat = match ($interval) {
                'day' => '%Y-%m-%d',
                'week' => '%x-W%v',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };

            if ($metric === 'package_type') {
                return $this->packageTypeTrends($groupFormat, $from, $to);
            }

            return Order::select(
                    DB::raw("DATE_FORMAT(date_purchased, '{$groupFormat}') as period"),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(CASE WHEN orders_status = 4 THEN 1 ELSE 0 END) as paid_orders'),
                    DB::raw('SUM(CASE WHEN orders_status = 3 THEN 1 ELSE 0 END) as shipped_orders'),
                    DB::raw('SUM(CASE WHEN orders_status != 5 THEN 1 ELSE 0 END) as active_orders'),
                    DB::raw('ROUND(AVG(NULLIF(weight_oz, 0)), 1) as avg_weight_oz')
                )
                ->whereBetween('date_purchased', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        });

        return response()->json($data);
    }

    /**
     * GET /reports/api/customers?metric=signups&interval=month&from=...&to=...
     *
     * Customer analytics time-series.
     */
    public function customers(Request $request): JsonResponse
    {
        $interval = $request->input('interval', 'month');
        $from = $request->input('from', now()->subYear()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $cacheKey = "reports:customers:{$interval}:{$from}:{$to}";

        $data = Cache::remember($cacheKey, 1800, function () use ($interval, $from, $to) {
            $groupFormat = match ($interval) {
                'day' => '%Y-%m-%d',
                'week' => '%x-W%v',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };

            $signups = CustomersInfo::select(
                    DB::raw("DATE_FORMAT(customers_info_date_account_created, '{$groupFormat}') as period"),
                    DB::raw('COUNT(*) as count')
                )
                ->whereBetween('customers_info_date_account_created', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            // Build cumulative totals
            $cumulative = [];
            $runningTotal = CustomersInfo::where('customers_info_date_account_created', '<', $from . ' 00:00:00')->count();
            foreach ($signups as $row) {
                $runningTotal += $row->count;
                $cumulative[] = [
                    'period' => $row->period,
                    'count' => $row->count,
                    'cumulative' => $runningTotal,
                ];
            }

            return $cumulative;
        });

        return response()->json($data);
    }

    /**
     * GET /reports/api/orders?page=1&per_page=50&sort=date_purchased&dir=desc&status=...&from=...&to=...&q=...
     *
     * Server-side paginated, sorted, filtered orders table.
     */
    public function orders(Request $request): JsonResponse
    {
        $query = Order::select([
                'orders_id', 'customers_id', 'customers_name',
                'date_purchased', 'orders_status',
                'package_type', 'weight_oz',
                'delivery_city', 'delivery_state', 'delivery_country',
                'ups_track_num', 'usps_track_num', 'usps_track_num_in',
                'fedex_track_num', 'dhl_track_num', 'amazon_track_num',
                'mail_class',
            ]);

        // Filters
        if ($from = $request->input('from')) {
            $query->where('date_purchased', '>=', $from . ' 00:00:00');
        }
        if ($to = $request->input('to')) {
            $query->where('date_purchased', '<=', $to . ' 23:59:59');
        }
        if ($status = $request->input('status')) {
            $query->where('orders_status', $status);
        }
        if ($type = $request->input('package_type')) {
            $query->where('package_type', $type);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('customers_name', 'LIKE', "%{$search}%")
                  ->orWhere('ups_track_num', 'LIKE', "%{$search}%")
                  ->orWhere('usps_track_num', 'LIKE', "%{$search}%")
                  ->orWhere('fedex_track_num', 'LIKE', "%{$search}%")
                  ->orWhere('dhl_track_num', 'LIKE', "%{$search}%")
                  ->orWhere('amazon_track_num', 'LIKE', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'date_purchased');
        $dir = $request->input('dir', 'desc');
        $allowed = ['date_purchased', 'orders_id', 'customers_name', 'orders_status', 'package_type', 'weight_oz'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->input('per_page', 50), 100);

        return response()->json($query->paginate($perPage));
    }

    /**
     * GET /reports/api/export?format=csv&status=...&from=...&to=...
     *
     * Streamed CSV export — never loads all rows into memory.
     */
    public function export(Request $request): StreamedResponse
    {
        $statuses = OrderStatus::pluck('orders_status_name', 'orders_status_id')->toArray();

        return response()->streamDownload(function () use ($request, $statuses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Date', 'Customer', 'Tracking #', 'Size', 'Weight (oz)', 'Status', 'Carrier', 'Destination']);

            Order::query()
                ->when($request->from, fn ($q, $v) => $q->where('date_purchased', '>=', $v . ' 00:00:00'))
                ->when($request->to, fn ($q, $v) => $q->where('date_purchased', '<=', $v . ' 23:59:59'))
                ->when($request->status, fn ($q, $v) => $q->where('orders_status', $v))
                ->when($request->package_type, fn ($q, $v) => $q->where('package_type', $v))
                ->orderBy('date_purchased', 'desc')
                ->chunk(500, function ($orders) use ($handle, $statuses) {
                    foreach ($orders as $order) {
                        $tracking = $order->usps_track_num ?: $order->ups_track_num ?: $order->fedex_track_num ?: $order->dhl_track_num ?: $order->amazon_track_num ?: '';
                        $carrier = match (true) {
                            !empty($order->usps_track_num) => 'USPS',
                            !empty($order->ups_track_num) => 'UPS',
                            !empty($order->fedex_track_num) => 'FedEx',
                            !empty($order->dhl_track_num) => 'DHL',
                            !empty($order->amazon_track_num) => 'Amazon',
                            default => '',
                        };
                        fputcsv($handle, [
                            $order->orders_id,
                            $order->date_purchased?->format('Y-m-d H:i'),
                            $order->customers_name,
                            $tracking,
                            $order->package_type,
                            $order->weight_oz,
                            $statuses[$order->orders_status] ?? "Status {$order->orders_status}",
                            $carrier,
                            trim("{$order->delivery_city}, {$order->delivery_state} {$order->delivery_country}"),
                        ]);
                    }
                });

            fclose($handle);
        }, 'orders-export-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * GET /reports/api/destinations?from=...&to=...&limit=20
     *
     * Top destination zip codes by order volume.
     */
    public function destinations(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->subYear()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
        $limit = min((int) $request->input('limit', 20), 50);

        $cacheKey = "reports:destinations:{$from}:{$to}:{$limit}";

        $data = Cache::remember($cacheKey, 1800, function () use ($from, $to, $limit) {
            return Order::select(
                    'delivery_postcode',
                    'delivery_state',
                    DB::raw('COUNT(*) as count'),
                    DB::raw('AVG(weight_oz) as avg_weight_oz')
                )
                ->whereBetween('date_purchased', [$from . ' 00:00:00', $to . ' 23:59:59'])
                ->where('delivery_postcode', '!=', '')
                ->whereNotNull('delivery_postcode')
                ->groupBy('delivery_postcode', 'delivery_state')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->map(fn ($row) => [
                    'zip' => $row->delivery_postcode,
                    'state' => $row->delivery_state,
                    'count' => $row->count,
                    'avg_weight_oz' => round((float) $row->avg_weight_oz, 1),
                ]);
        });

        return response()->json($data);
    }

    // ---------------------------------------------------------------
    // Private Helpers
    // ---------------------------------------------------------------

    /**
     * Convert a range string (30d, 90d, 12m) to date boundaries.
     *
     * @return array [$from, $to, $prevFrom, $prevTo]
     */
    private function rangeToDates(string $range): array
    {
        $to = now()->endOfDay();

        $from = match ($range) {
            '7d' => now()->subDays(7)->startOfDay(),
            '90d' => now()->subDays(90)->startOfDay(),
            '12m' => now()->subYear()->startOfDay(),
            default => now()->subDays(30)->startOfDay(),
        };

        $diff = $from->diffInDays($to);
        $prevTo = (clone $from)->subDay()->endOfDay();
        $prevFrom = (clone $prevTo)->subDays($diff)->startOfDay();

        return [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
            $prevFrom->format('Y-m-d H:i:s'),
            $prevTo->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Package type breakdown over time for stacked chart.
     */
    private function packageTypeTrends(string $groupFormat, string $from, string $to): array
    {
        $rows = Order::select(
                DB::raw("DATE_FORMAT(date_purchased, '{$groupFormat}') as period"),
                'package_type',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('date_purchased', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('period', 'package_type')
            ->orderBy('period')
            ->get();

        // Pivot into { period, type1: count, type2: count, ... }
        $periods = $rows->groupBy('period')->map(function ($group, $period) {
            $entry = ['period' => $period];
            foreach ($group as $row) {
                $type = $row->package_type ?: 'Unknown';
                $entry[$type] = $row->count;
            }
            return $entry;
        });

        return $periods->values()->toArray();
    }
}
