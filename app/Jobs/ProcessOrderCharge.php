<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOrderCharge implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $orderId,
        public float $amount,
    ) {
        $this->onQueue('payments');
    }

    public function handle(PaymentService $paymentService): void
    {
        $order = Order::findOrFail($this->orderId);
        $customer = $order->customer;

        if (!$customer || empty($customer->card_token)) {
            Log::channel('payment')->error("No card token for order #{$this->orderId}");
            $this->fail(new \Exception("No card token for order #{$this->orderId}"));
            return;
        }

        try {
            $result = $paymentService->chargeCard(
                $customer->card_token,
                $this->amount,
                "Order #{$this->orderId}"
            );

            if ($result) {
                $order->update([
                    'orders_status' => 4, // Paid
                    'billing_status' => 1,
                    'last_modified' => now(),
                ]);

                OrderStatusHistory::record($this->orderId, 4, 'Payment processed successfully');

                Log::channel('payment')->info("Order #{$this->orderId} charged \${$this->amount}");
            }
        } catch (\Exception $e) {
            Log::channel('payment')->error("Charge failed for order #{$this->orderId}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('payment')->error("Order #{$this->orderId} charge permanently failed: {$exception->getMessage()}");

        // Update order status to awaiting payment
        Order::where('orders_id', $this->orderId)->update([
            'orders_status' => 2,
            'last_modified' => now(),
        ]);

        OrderStatusHistory::record($this->orderId, 2, 'Automatic payment failed: ' . $exception->getMessage());
    }
}
