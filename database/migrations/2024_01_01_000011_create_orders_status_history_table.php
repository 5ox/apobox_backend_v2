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
        Schema::create('orders_status_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('orders_status_history_id');
            $table->bigInteger('orders_id')->unsigned()->default(0);
            $table->integer('orders_status_id')->default(0);
            $table->dateTime('date_added');
            $table->integer('customer_notified')->default(0);
            $table->text('comments')->nullable();

            $table->index('orders_id', 'idx_orders_id');
            $table->foreign('orders_id')
                  ->references('orders_id')
                  ->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_status_history');
    }
};
