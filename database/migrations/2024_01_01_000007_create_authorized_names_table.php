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
        Schema::create('authorized_names', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('authorized_names_id');
            $table->integer('customers_id')->default(0);
            $table->string('authorized_firstname', 20)->default('');
            $table->string('authorized_lastname', 20)->default('');

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
        Schema::dropIfExists('authorized_names');
    }
};
