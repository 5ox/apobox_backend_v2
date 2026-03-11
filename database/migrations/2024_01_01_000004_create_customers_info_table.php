<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers_info', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('customers_info_id')->unsigned()->primary();
            $table->dateTime('customers_info_date_of_last_logon')->nullable();
            $table->integer('customers_info_number_of_logons')->nullable();
            $table->dateTime('customers_info_date_account_created')->nullable();
            $table->dateTime('customers_info_date_account_last_modified')->nullable();
            $table->integer('customers_info_source_id')->default(0);
            $table->integer('global_product_notifications')->default(0);
            $table->string('IP_signup', 15)->default('');
            $table->string('IP_lastlogon', 15)->default('');
            $table->string('IP_cc_update', 15)->default('');
            $table->string('IP_addressbook_update', 15)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_info');
    }
};
