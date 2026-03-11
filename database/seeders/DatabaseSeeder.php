<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            ZoneSeeder::class,
            InsuranceSeeder::class,
            OrderStatusSeeder::class,
        ]);
    }
}
