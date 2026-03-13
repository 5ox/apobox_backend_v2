<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyStorageFees extends Command
{
    protected $signature = 'app:apply-storage-fees
        {--dry-run : Show what would happen without making changes}';

    protected $description = 'Accrue daily storage fees on warehouse orders past grace period and auto-charge';

    public function handle(): int
    {
        $dailyRate = config('apobox.orders.storage.daily_rate', 2.00);
        $graceDays = config('apobox.orders.storage.grace_days', 14);
        $dryRun = $this->option('dry-run');
        $cutoff = today()->subDays($graceDays);

        $this->info(sprintf(
            'Storage fees: $%.2f/day, %d-day grace, cutoff: %s%s',
            $dailyRate, $graceDays, $cutoff->toDateString(), $dryRun ? ' [DRY RUN]' : ''
        ));

        // Find warehouse orders past the grace period, with storage line item and customer
        $orders = Order::with(['storage', 'customer', 'total'])
            ->where('orders_status', 1) // Warehouse
            ->whereDate('date_purchased', '<=', $cutoff)
            ->get();

        $stats = ['checked' => $orders->count(), 'updated' => 0, 'charged' => 0, 'failed' => 0, 'skipped' => 0];

        foreach ($orders as $order) {
            $totalDays = (int) $order->date_purchased->diffInDays(today());
            $billableDays = max(0, $totalDays - $graceDays);
            $newStorageTotal = round($billableDays * $dailyRate, 2);

            $currentStorage = $order->storage?->value ?? 0;

            // Skip if storage is already at or above calculated amount
            if ($currentStorage >= $newStorageTotal) {
                $stats['skipped']++;
                continue;
            }

            $this->line(sprintf(
                '  Order #%s: %d days in warehouse, %d billable → $%.2f (was $%.2f)',
                $order->orders_id, $totalDays, $billableDays, $newStorageTotal, $currentStorage
            ));

            if ($dryRun) {
                $stats['updated']++;
                continue;
            }

            // Update the storage line item (observer auto-recalculates subtotal/total)
            if ($order->storage) {
                $order->storage->update([
                    'value' => $newStorageTotal,
                    'text' => '$' . number_format($newStorageTotal, 2),
                ]);
            }

            // Reload total after recalculation
            $order->refresh();
            $stats['updated']++;

            Log::channel('payment')->info('Storage fee updated', [
                'order_id' => $order->orders_id,
                'days' => $totalDays,
                'billable_days' => $billableDays,
                'storage_fee' => $newStorageTotal,
                'total' => $order->total?->value,
            ]);

            // Attempt auto-charge
            $cardToken = $order->customer?->card_token;
            $totalAmount = $order->total?->value ?? 0;

            if (!$cardToken || $totalAmount <= 0) {
                // No card on file — move to awaiting payment
                $order->update(['orders_status' => 2, 'last_modified' => now()]);
                OrderStatusHistory::record(
                    $order->orders_id, 2,
                    sprintf('Storage fee $%.2f accrued (%d days). No card on file.', $newStorageTotal, $billableDays)
                );
                $stats['failed']++;
                $this->warn("    → No card on file, set to Awaiting Payment");
                continue;
            }

            try {
                $paymentService = app(PaymentService::class);
                $result = $paymentService->chargeCard(
                    $cardToken,
                    $totalAmount,
                    'Order #' . $order->orders_id . ' (storage fee)'
                );

                if ($result['success']) {
                    $order->update([
                        'orders_status' => 4,
                        'payment_method' => 'cc',
                        'trans_id' => $result['payment_id'] ?? '',
                        'last_modified' => now(),
                    ]);
                    OrderStatusHistory::record(
                        $order->orders_id, 4,
                        sprintf('Auto-charged $%.2f (storage: $%.2f, %d days)', $totalAmount, $newStorageTotal, $billableDays)
                    );
                    $stats['charged']++;
                    $this->info("    → Charged \${$totalAmount} successfully");
                } else {
                    $error = $result['error'] ?? 'Unknown error';
                    $order->update(['orders_status' => 2, 'last_modified' => now()]);
                    OrderStatusHistory::record(
                        $order->orders_id, 2,
                        sprintf('Storage fee $%.2f accrued. Auto-charge failed: %s', $newStorageTotal, $error)
                    );
                    $stats['failed']++;
                    $this->warn("    → Charge failed: {$error}, set to Awaiting Payment");
                }
            } catch (\Exception $e) {
                Log::channel('payment')->error('Storage auto-charge exception', [
                    'order_id' => $order->orders_id,
                    'error' => $e->getMessage(),
                ]);
                $order->update(['orders_status' => 2, 'last_modified' => now()]);
                OrderStatusHistory::record(
                    $order->orders_id, 2,
                    sprintf('Storage fee $%.2f accrued. Auto-charge error: %s', $newStorageTotal, $e->getMessage())
                );
                $stats['failed']++;
                $this->error("    → Exception: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. Checked: %d, Updated: %d, Charged: %d, Failed: %d, Skipped: %d',
            $stats['checked'], $stats['updated'], $stats['charged'], $stats['failed'], $stats['skipped']
        ));

        return self::SUCCESS;
    }
}
