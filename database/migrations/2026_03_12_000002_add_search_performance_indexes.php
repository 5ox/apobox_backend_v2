<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orderIndexes = [
            'idx_usps_track'    => 'usps_track_num',
            'idx_usps_track_in' => 'usps_track_num_in',
            'idx_ups_track'     => 'ups_track_num',
            'idx_fedex_track'   => 'fedex_track_num',
            'idx_dhl_track'     => 'dhl_track_num',
        ];

        foreach ($orderIndexes as $name => $column) {
            if (!$this->indexExists('orders', $name)) {
                Schema::table('orders', fn (Blueprint $t) => $t->index($column, $name));
            }
        }

        if (!$this->indexExists('customers', 'idx_customer_name')) {
            Schema::table('customers', fn (Blueprint $t) => $t->index(['customers_lastname', 'customers_firstname'], 'idx_customer_name'));
        }

        if (!$this->indexExists('customers', 'idx_backup_email')) {
            Schema::table('customers', fn (Blueprint $t) => $t->index('backup_email_address', 'idx_backup_email'));
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_usps_track');
            $table->dropIndex('idx_usps_track_in');
            $table->dropIndex('idx_ups_track');
            $table->dropIndex('idx_fedex_track');
            $table->dropIndex('idx_dhl_track');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customer_name');
            $table->dropIndex('idx_backup_email');
        });
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
