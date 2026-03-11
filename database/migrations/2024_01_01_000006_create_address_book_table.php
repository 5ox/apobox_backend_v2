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
        Schema::create('address_book', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('address_book_id');
            $table->integer('customers_id')->default(0);
            $table->string('entry_gender', 1)->default('');
            $table->string('entry_company', 32)->nullable();
            $table->string('entry_firstname', 32)->default('');
            $table->string('entry_lastname', 32)->default('');
            $table->string('entry_street_address', 64)->default('');
            $table->string('entry_suburb', 32)->default('');
            $table->string('entry_postcode', 10)->default('');
            $table->string('entry_city', 32)->default('');
            $table->string('entry_state', 32)->default('');
            $table->integer('entry_country_id')->default(0);
            $table->integer('entry_zone_id')->default(0);
            $table->string('entry_basename', 255)->default('');

            $table->index('customers_id', 'idx_customers_id');
            $table->foreign('customers_id')
                  ->references('customers_id')
                  ->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_book');
    }
};
