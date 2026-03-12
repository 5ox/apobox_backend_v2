<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['orders_status_id' => 1, 'language_id' => 1, 'orders_status_name' => 'Warehouse'],
            ['orders_status_id' => 2, 'language_id' => 1, 'orders_status_name' => 'Awaiting Payment'],
            ['orders_status_id' => 3, 'language_id' => 1, 'orders_status_name' => 'Shipped'],
            ['orders_status_id' => 4, 'language_id' => 1, 'orders_status_name' => 'Paid'],
            ['orders_status_id' => 5, 'language_id' => 1, 'orders_status_name' => 'Returned'],
            ['orders_status_id' => 6, 'language_id' => 1, 'orders_status_name' => 'Problem'],
        ];

        DB::table('orders_status')->insertOrIgnore($statuses);
    }
}
