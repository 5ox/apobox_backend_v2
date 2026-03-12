<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Uses raw CREATE INDEX instead of Schema::table to avoid MySQL strict mode
 * re-validating legacy columns (customers_dob has invalid 0000-00-00 default).
 */
return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            ['orders', 'idx_usps_track',    'usps_track_num'],
            ['orders', 'idx_usps_track_in', 'usps_track_num_in'],
            ['orders', 'idx_ups_track',     'ups_track_num'],
            ['orders', 'idx_fedex_track',   'fedex_track_num'],
            ['orders', 'idx_dhl_track',     'dhl_track_num'],
            ['customers', 'idx_customer_name', 'customers_lastname, customers_firstname'],
            ['customers', 'idx_backup_email',  'backup_email_address'],
        ];

        foreach ($indexes as [$table, $name, $columns]) {
            if (!$this->indexExists($table, $name)) {
                DB::statement("CREATE INDEX `{$name}` ON `{$table}` ({$columns})");
            }
        }
    }

    public function down(): void
    {
        $indexes = [
            ['orders', 'idx_usps_track'],
            ['orders', 'idx_usps_track_in'],
            ['orders', 'idx_ups_track'],
            ['orders', 'idx_fedex_track'],
            ['orders', 'idx_dhl_track'],
            ['customers', 'idx_customer_name'],
            ['customers', 'idx_backup_email'],
        ];

        foreach ($indexes as [$table, $name]) {
            if ($this->indexExists($table, $name)) {
                DB::statement("DROP INDEX `{$name}` ON `{$table}`");
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $db = config('database.connections.mysql.database', env('DB_DATABASE'));
        $result = DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$db, $table, $indexName]
        );

        return $result[0]->cnt > 0;
    }
};
