<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CustomersInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerInfoController extends Controller
{
    /**
     * Valid reporting intervals.
     */
    protected array $validIntervals = ['day', 'week', 'month', 'year'];

    /**
     * Valid sort fields.
     */
    protected array $validSortFields = [
        'customers_info_date_account_created',
        'customers_info_date_of_last_logon',
    ];

    /**
     * Show the customer info report.
     */
    public function report(Request $request): View
    {
        $validIntervals = $this->validIntervals;
        $validSortFields = $this->validSortFields;
        $results = null;
        $interval = 'day';

        $data = $request->all();

        if (!empty($data)) {
            $interval = $data['interval'] ?? 'day';
            $fromDate = $data['from_date'] ?? now()->subMonths(3)->format('Y-m-d');
            $toDate = $data['to_date'] ?? now()->format('Y-m-d');
            $sortField = in_array($data['sort'] ?? '', $this->validSortFields)
                ? $data['sort']
                : 'customers_info_date_account_created';

            $dateFormat = match ($interval) {
                'day' => '%Y-%m-%d',
                'week' => '%x-W%v',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };

            $results = CustomersInfo::select(
                    DB::raw("DATE_FORMAT({$sortField}, '{$dateFormat}') as period"),
                    DB::raw('COUNT(*) as count')
                )
                ->whereBetween($sortField, [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        return view('manager.customer-info.report', compact(
            'validIntervals',
            'validSortFields',
            'results',
            'interval'
        ));
    }
}
