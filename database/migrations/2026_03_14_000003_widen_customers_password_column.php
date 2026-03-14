<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `customers` MODIFY `customers_password` VARCHAR(255) NOT NULL DEFAULT ''");
    }

    public function down(): void
    {
        DB::statement("SET SESSION sql_mode = ''");
        DB::statement("ALTER TABLE `customers` MODIFY `customers_password` VARCHAR(40) NOT NULL DEFAULT ''");
    }
};
