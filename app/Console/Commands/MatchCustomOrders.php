<?php

namespace App\Console\Commands;

use App\Models\CustomPackageRequest;
use App\Models\Order;
use Illuminate\Console\Command;

class MatchCustomOrders extends Command
{
    protected $signature = 'app:match-custom-orders';
    protected $description = 'Match custom package requests to orders by tracking number';

    public function handle(): int
    {
        $unmatched = CustomPackageRequest::where('orders_id', '0')
            ->orWhere('orders_id', '')
            ->get();

        $matched = 0;
        foreach ($unmatched as $request) {
            if (empty($request->tracking_id) || $request->tracking_id === '0') {
                continue;
            }

            // Look for orders with matching inbound tracking
            $order = Order::where('usps_track_num_in', $request->tracking_id)
                ->orWhere('ups_track_num', $request->tracking_id)
                ->orWhere('dhl_track_num', $request->tracking_id)
                ->first();

            if ($order) {
                $request->update(['orders_id' => $order->orders_id]);
                $matched++;
            }
        }

        $this->info("Matched {$matched} of {$unmatched->count()} custom orders.");
        return self::SUCCESS;
    }
}
