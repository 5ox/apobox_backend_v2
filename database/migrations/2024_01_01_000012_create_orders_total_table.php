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
        Schema::create('orders_total', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('orders_total_id');
            $table->bigInteger('orders_id')->unsigned()->default(0);
            $table->string('title', 255)->default('');
            $table->string('text', 255)->default('');
            $table->decimal('value', 15, 4)->default(0.0000);
            $table->string('class', 32)->default('');
            $table->integer('sort_order')->default(0);

            $table->index('orders_id', 'idx_orders_total_orders_id');
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
        Schema::dropIfExists('orders_total');
    }
};
