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
        Schema::create('orders_data', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->bigInteger('orders_id')->unsigned();
            $table->string('data_key', 64);
            $table->text('data_value');
            $table->timestamps();

            $table->index('orders_id', 'idx_orders_data_order');
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
        Schema::dropIfExists('orders_data');
    }
};
