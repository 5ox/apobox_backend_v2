<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\SearchIndex;
use Illuminate\Console\Command;

class RebuildSearchIndex extends Command
{
    protected $signature = 'app:rebuild-search-index';
    protected $description = 'Rebuild the full-text search index for all customers';

    public function handle(): int
    {
        $this->info('Rebuilding search index...');

        // Clear all customer entries
        SearchIndex::where('model', Customer::class)->delete();
        // Also clear legacy entries that used the short class name
        SearchIndex::where('model', 'Customer')->delete();

        $total = Customer::count();
        $bar = $this->output->createProgressBar($total);
        $indexed = 0;

        Customer::with('authorizedNames')->chunk(200, function ($customers) use ($bar, &$indexed) {
            foreach ($customers as $customer) {
                SearchIndex::updateIndex(
                    Customer::class,
                    $customer->customers_id,
                    $customer->indexData()
                );
                $bar->advance();
                $indexed++;
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Rebuilt index for {$indexed} customers.");

        return self::SUCCESS;
    }
}
