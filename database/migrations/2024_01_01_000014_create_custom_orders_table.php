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
        Schema::create('custom_orders', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('custom_orders_id');
            $table->integer('customers_id')->default(0);
            $table->string('tracking_id', 30)->default('0');
            $table->string('billing_id', 20)->default('0');
            $table->string('orders_id', 30)->default('0');
            $table->integer('package_status')->default(0);
            $table->string('package_repack', 4)->default('');
            $table->string('insurance_fee', 10)->default('');
            $table->string('insurance_coverage', 10)->default('');
            $table->string('mail_class', 15)->default('');
            $table->text('instructions');
            $table->timestamp('order_add_date')->useCurrent();

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
        Schema::dropIfExists('custom_orders');
    }
};
