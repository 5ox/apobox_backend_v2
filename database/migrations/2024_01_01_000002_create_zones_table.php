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
        Schema::create('zones', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('zone_id');
            $table->integer('zone_country_id')->default(0);
            $table->string('zone_code', 32)->default('');
            $table->string('zone_name', 32)->default('');

            $table->foreign('zone_country_id')
                  ->references('countries_id')
                  ->on('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zones');
    }
};
