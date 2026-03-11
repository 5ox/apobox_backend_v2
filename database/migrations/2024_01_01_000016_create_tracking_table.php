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
        Schema::create('tracking', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->string('tracking_id', 40)->default('')->primary();
            $table->string('warehouse', 30)->default('Bancroft');
            $table->timestamp('timestamp')->useCurrent()->useCurrentOnUpdate();
            $table->string('comments', 200)->nullable();
            $table->string('shipped', 5)->default('0');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking');
    }
};
