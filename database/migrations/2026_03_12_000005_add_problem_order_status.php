<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders_status')->insertOrIgnore([
            'orders_status_id' => 6,
            'language_id' => 1,
            'orders_status_name' => 'Problem',
        ]);
    }

    public function down(): void
    {
        DB::table('orders_status')->where('orders_status_id', 6)->delete();
    }
};
