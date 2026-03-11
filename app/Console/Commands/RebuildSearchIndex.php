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

        SearchIndex::where('model', 'Customer')->delete();

        $customers = Customer::with('authorizedNames')->get();
        $bar = $this->output->createProgressBar($customers->count());

        foreach ($customers as $customer) {
            SearchIndex::updateOrCreate(
                ['model' => 'Customer', 'foreign_key' => $customer->customers_id],
                ['data' => $customer->indexData()]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Rebuilt index for {$customers->count()} customers.");

        return self::SUCCESS;
    }
}
