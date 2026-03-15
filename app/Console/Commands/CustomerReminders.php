<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerReminder;
use App\Models\Order;
use App\Mail\AwaitingPaymentAlert;
use App\Mail\PartialSignupAlert;
use App\Mail\CreditCardExpiring;
use App\Mail\CreditCardExpired;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CustomerReminders extends Command
{
    protected $signature = 'app:customer-reminders
        {--awaiting-payment : Send reminders for orders awaiting payment}
        {--partial-signups : Send reminders for incomplete signups}
        {--expiring-cards : Send reminders for expiring credit cards}
        {--purge-partials : Delete expired partial signups}';

    protected $description = 'Send customer reminder emails and purge expired records';

    public function handle(): int
    {
        if ($this->option('awaiting-payment')) {
            return $this->awaitingPayment();
        }
        if ($this->option('partial-signups')) {
            return $this->partialSignups();
        }
        if ($this->option('expiring-cards')) {
            return $this->expiringCards();
        }
        if ($this->option('purge-partials')) {
            return $this->purgePartials();
        }

        $this->error('Please specify an option: --awaiting-payment, --partial-signups, --expiring-cards, or --purge-partials');
        return self::FAILURE;
    }

    protected function awaitingPayment(): int
    {
        $maxReminders = config('apobox.orders.payment_reminders', 3);

        // Find orders with status=2 (Awaiting Payment)
        $orders = Order::with('customer')
            ->where('orders_status', 2)
            ->get();

        $sent = 0;
        foreach ($orders as $order) {
            if (!$order->customer) {
                continue;
            }

            $reminder = CustomerReminder::firstOrCreate(
                [
                    'customers_id' => $order->customers_id,
                    'reminder_type' => 'awaiting_payment',
                    'orders_id' => $order->orders_id,
                ],
                ['reminder_count' => 0]
            );

            if ($reminder->reminder_count >= $maxReminders) {
                continue;
            }

            try {
                Mail::to($order->customer->customers_email_address)
                    ->queue(new AwaitingPaymentAlert(
                        $order->customer->full_name,
                        (string) $order->orders_id,
                        url('/orders/' . $order->orders_id . '/pay'),
                        $order->comments
                    ));

                $reminder->increment('reminder_count');
                $sent++;

                Log::channel('email')->info('Awaiting payment reminder sent', [
                    'customers_id' => $order->customers_id,
                    'orders_id' => $order->orders_id,
                    'reminder_count' => $reminder->reminder_count,
                ]);
            } catch (\Exception $e) {
                Log::channel('email')->error('Failed to send awaiting payment reminder', [
                    'customers_id' => $order->customers_id,
                    'orders_id' => $order->orders_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} awaiting payment reminders.");
        return self::SUCCESS;
    }

    protected function partialSignups(): int
    {
        $maxReminders = config('apobox.customers.signup_reminders', 2);

        $customers = Customer::partialSignups()->get();

        $sent = 0;
        foreach ($customers as $customer) {
            $reminder = CustomerReminder::firstOrCreate(
                ['customers_id' => $customer->customers_id, 'reminder_type' => 'partial_signup'],
                ['reminder_count' => 0]
            );

            if ($reminder->reminder_count >= $maxReminders) {
                continue;
            }

            try {
                Mail::to($customer->customers_email_address)
                    ->queue(new PartialSignupAlert(
                        $customer->full_name,
                        url('/customers/account-incomplete')
                    ));

                $reminder->increment('reminder_count');
                $sent++;
            } catch (\Exception $e) {
                Log::channel('email')->error('Failed to send partial signup reminder', [
                    'customers_id' => $customer->customers_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} partial signup reminders.");
        return self::SUCCESS;
    }

    protected function expiringCards(): int
    {
        $config = config('apobox.customers.expired_card_reminders');
        $maxMonths = $config['max_months'] ?? 6;
        $maxPerRun = $config['send_max_per_run'] ?? 200;
        $delay = $config['send_delay_seconds'] ?? 2;
        $maxToSend = $config['number_to_send'] ?? 1;

        $customers = Customer::expiringCards($maxMonths)->limit($maxPerRun)->get();

        $sent = 0;
        foreach ($customers as $customer) {
            $reminder = CustomerReminder::firstOrCreate(
                ['customers_id' => $customer->customers_id, 'reminder_type' => 'expired_card'],
                ['reminder_count' => 0]
            );

            if ($reminder->reminder_count >= $maxToSend) {
                continue;
            }

            // Determine if card is already expired or expiring soon
            $isExpired = $customer->isCardExpired();
            $mailable = $isExpired
                ? new CreditCardExpired($customer->full_name, url('/customers/edit/payment_info'))
                : new CreditCardExpiring($customer->customers_firstname, $customer->customers_lastname);

            try {
                Mail::to($customer->customers_email_address)->queue($mailable);

                $reminder->increment('reminder_count');
                $sent++;

                if ($delay > 0) {
                    sleep($delay);
                }
            } catch (\Exception $e) {
                Log::channel('email')->error('Failed to send card expiry reminder', [
                    'customers_id' => $customer->customers_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sent {$sent} expiring card reminders.");
        return self::SUCCESS;
    }

    protected function purgePartials(): int
    {
        $weeks = config('apobox.customers.purge_partials_weeks', 4);
        $cutoff = now()->subWeeks($weeks);

        $count = Customer::partialSignups()
            ->where('created', '<', $cutoff)
            ->delete();

        $this->info("Purged {$count} expired partial signups.");
        return self::SUCCESS;
    }
}
