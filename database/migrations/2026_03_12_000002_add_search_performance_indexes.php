<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Temporarily disables strict mode so MySQL won't reject the ALTER/CREATE INDEX
 * due to customers_dob having an invalid 0000-00-00 default from the CakePHP era.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Save current mode, disable strict for this migration
        $currentMode = DB::selectOne("SELECT @@SESSION.sql_mode as m")->m;
        DB::statement("SET SESSION sql_mode = ''");

        try {
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
        } finally {
            // Restore strict mode
            DB::statement("SET SESSION sql_mode = ?", [$currentMode]);
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
