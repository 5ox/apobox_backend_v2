<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add index on creator_id for employee activity reports.
 * The composite (date_purchased, creator_id) index speeds up the
 * GROUP BY query used for the 30-day employee activity breakdown.
 */
return new class extends Migration
{
    public function up(): void
    {
        $currentMode = DB::selectOne("SELECT @@SESSION.sql_mode as m")->m;
        DB::statement("SET SESSION sql_mode = ''");

        try {
            $indexes = [
                ['orders', 'idx_creator_id',           'creator_id'],
                ['orders', 'idx_date_creator',         'date_purchased, creator_id'],
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
            ['orders', 'idx_creator_id'],
            ['orders', 'idx_date_creator'],
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
