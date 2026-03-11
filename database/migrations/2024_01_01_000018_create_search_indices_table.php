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
        Schema::create('search_indices', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('model', 64);
            $table->integer('foreign_key');
            $table->text('data');
            $table->timestamps();

            $table->unique(['model', 'foreign_key'], 'uniq_model_fk');
            $table->fullText('data', 'ft_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_indices');
    }
};
