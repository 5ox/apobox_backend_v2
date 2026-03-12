<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes for order search — tracking number LIKE queries
        Schema::table('orders', function (Blueprint $table) {
            $table->index('usps_track_num', 'idx_usps_track');
            $table->index('usps_track_num_in', 'idx_usps_track_in');
            $table->index('ups_track_num', 'idx_ups_track');
            $table->index('fedex_track_num', 'idx_fedex_track');
            $table->index('dhl_track_num', 'idx_dhl_track');
        });

        // Composite index for customer name search
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['customers_lastname', 'customers_firstname'], 'idx_customer_name');
            $table->index('backup_email_address', 'idx_backup_email');
        });
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
};
