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
        Schema::create('insurance', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('insurance_id');
            $table->decimal('amount_from', 15, 2)->default(0.00);
            $table->decimal('amount_to', 15, 2)->default(0.00);
            $table->decimal('insurance_fee', 15, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance');
    }
};
