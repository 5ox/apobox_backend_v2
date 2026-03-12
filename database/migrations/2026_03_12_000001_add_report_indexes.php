<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index('date_purchased', 'idx_date_purchased');
            $table->index(['date_purchased', 'orders_status'], 'idx_date_status');
        });

        Schema::table('customers_info', function (Blueprint $table) {
            $table->index('customers_info_date_account_created', 'idx_date_account_created');
            $table->index('customers_info_date_of_last_logon', 'idx_date_last_logon');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_date_purchased');
            $table->dropIndex('idx_date_status');
        });

        Schema::table('customers_info', function (Blueprint $table) {
            $table->dropIndex('idx_date_account_created');
            $table->dropIndex('idx_date_last_logon');
        });
    }
};
