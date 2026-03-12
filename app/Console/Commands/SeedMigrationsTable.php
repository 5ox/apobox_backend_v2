<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedMigrationsTable extends Command
{
    protected $signature = 'migrate:seed-existing';
    protected $description = 'Create the migrations table and mark all existing migrations as run (for legacy DB)';

    public function handle(): int
    {
        // Ensure the migrations table exists
        if (!Schema::hasTable('migrations')) {
            $this->call('migrate:install');
        }

        $migrations = [
            '2024_01_01_000001_create_countries_table',
            '2024_01_01_000002_create_zones_table',
            '2024_01_01_000003_create_customers_table',
            '2024_01_01_000004_create_customers_info_table',
            '2024_01_01_000005_create_customer_reminders_table',
            '2024_01_01_000006_create_address_book_table',
            '2024_01_01_000007_create_authorized_names_table',
            '2024_01_01_000008_create_admins_table',
            '2024_01_01_000009_create_orders_table',
            '2024_01_01_000010_create_orders_status_table',
            '2024_01_01_000011_create_orders_status_history_table',
            '2024_01_01_000012_create_orders_total_table',
            '2024_01_01_000013_create_orders_data_table',
            '2024_01_01_000014_create_custom_orders_table',
            '2024_01_01_000015_create_insurance_table',
            '2024_01_01_000016_create_tracking_table',
            '2024_01_01_000017_create_password_requests_table',
            '2024_01_01_000018_create_search_indices_table',
            '2024_01_01_000019_create_affiliate_links_table',
            '2024_01_01_000020_create_laravel_system_tables',
        ];

        $existing = DB::table('migrations')->pluck('migration')->toArray();
        $inserted = 0;

        foreach ($migrations as $migration) {
            if (!in_array($migration, $existing)) {
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => 1,
                ]);
                $inserted++;
            }
        }

        $this->info("Seeded {$inserted} migrations (" . count($existing) . " already existed).");

        return self::SUCCESS;
    }
}
