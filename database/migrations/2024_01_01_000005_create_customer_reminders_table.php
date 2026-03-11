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
        Schema::create('customer_reminders', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('customers_id');
            $table->string('type', 32);
            $table->integer('count')->default(0);
            $table->dateTime('last_sent_at')->nullable();
            $table->timestamps();

            $table->index(['customers_id', 'type'], 'idx_customer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_reminders');
    }
};
