<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomersInfo;
use Illuminate\Console\Command;

class SyncCustomersInfo extends Command
{
    protected $signature = 'app:sync-customers-info';
    protected $description = 'Ensure all customers have a corresponding customers_info record';

    public function handle(): int
    {
        $created = 0;
        Customer::chunk(500, function ($customers) use (&$created) {
            foreach ($customers as $customer) {
                $exists = CustomersInfo::where('customers_info_id', $customer->customers_id)->exists();
                if (!$exists) {
                    CustomersInfo::create([
                        'customers_info_id' => $customer->customers_id,
                        'customers_info_date_account_created' => $customer->created_at ?? now(),
                    ]);
                    $created++;
                }
            }
        });

        $this->info("Created {$created} missing customers_info records.");
        return self::SUCCESS;
    }
}
