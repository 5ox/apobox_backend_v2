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
        Schema::create('countries', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('countries_id');
            $table->string('countries_name', 64)->default('');
            $table->char('countries_iso_code_2', 2)->default('');
            $table->char('countries_iso_code_3', 3)->default('');
            $table->integer('address_format_id')->default(0);

            $table->index('countries_name', 'IDX_COUNTRIES_NAME');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
