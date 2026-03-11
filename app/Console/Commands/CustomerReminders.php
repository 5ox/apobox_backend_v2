<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerReminder;
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

        // TODO: Port from CakePHP CustomerReminderShell::awaiting_payment()
        // Find orders with status=2 (Awaiting Payment) where reminders < max
        // Send email and increment reminder count
        $this->info('Awaiting payment reminders: TODO');

        return self::SUCCESS;
    }

    protected function partialSignups(): int
    {
        $maxReminders = config('apobox.customers.signup_reminders', 2);

        $customers = Customer::partialSignups()->get();

        $sent = 0;
        foreach ($customers as $customer) {
            $reminder = CustomerReminder::firstOrCreate(
                ['customers_id' => $customer->customers_id, 'type' => 'partial_signup'],
                ['count' => 0]
            );

            if ($reminder->count >= $maxReminders) {
                continue;
            }

            // TODO: Send partial signup reminder email
            $reminder->increment('count');
            $reminder->update(['last_sent_at' => now()]);
            $sent++;
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

        $customers = Customer::expiringCards($maxMonths)->limit($maxPerRun)->get();

        $sent = 0;
        foreach ($customers as $customer) {
            $reminder = CustomerReminder::firstOrCreate(
                ['customers_id' => $customer->customers_id, 'type' => 'expired_card'],
                ['count' => 0]
            );

            if ($reminder->count >= ($config['number_to_send'] ?? 1)) {
                continue;
            }

            // TODO: Send expiring card email
            $reminder->increment('count');
            $reminder->update(['last_sent_at' => now()]);
            $sent++;

            if ($delay > 0) {
                sleep($delay);
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
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Purged {$count} expired partial signups.");
        return self::SUCCESS;
    }
}
