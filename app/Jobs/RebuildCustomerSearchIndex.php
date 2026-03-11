<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\SearchIndex;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RebuildCustomerSearchIndex implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $customerId,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $customer = Customer::find($this->customerId);
        if ($customer) {
            SearchIndex::updateIndex($customer);
        }
    }
}
