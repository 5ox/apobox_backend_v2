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
        Schema::create('orders_status', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->integer('orders_status_id')->primary();
            $table->integer('language_id')->default(1);
            $table->string('orders_status_name', 32)->default('');

            $table->index('orders_status_name', 'idx_orders_status_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_status');
    }
};
