<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add indexes for order filtering/sorting: orders_status, date_purchased, customers_id.
 * These are used on every search/list query.
 */
return new class extends Migration
{
    public function up(): void
    {
        $currentMode = DB::selectOne("SELECT @@SESSION.sql_mode as m")->m;
        DB::statement("SET SESSION sql_mode = ''");

        try {
            $indexes = [
                ['orders', 'idx_orders_status',    'orders_status'],
                ['orders', 'idx_date_purchased',   'date_purchased'],
                ['orders', 'idx_orders_customer',  'customers_id'],
            ];

            foreach ($indexes as [$table, $name, $columns]) {
                if (!$this->indexExists($table, $name)) {
                    DB::statement("CREATE INDEX `{$name}` ON `{$table}` ({$columns})");
                }
            }
        } finally {
            DB::statement("SET SESSION sql_mode = ?", [$currentMode]);
        }
    }

    public function down(): void
    {
        $indexes = [
            ['orders', 'idx_orders_status'],
            ['orders', 'idx_date_purchased'],
            ['orders', 'idx_orders_customer'],
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
