<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            ['insurance_id' => 1, 'amount_from' => 0.01, 'amount_to' => 50.00, 'insurance_fee' => 1.75],
            ['insurance_id' => 2, 'amount_from' => 50.01, 'amount_to' => 100.00, 'insurance_fee' => 2.25],
            ['insurance_id' => 3, 'amount_from' => 100.01, 'amount_to' => 200.00, 'insurance_fee' => 2.75],
            ['insurance_id' => 4, 'amount_from' => 200.01, 'amount_to' => 300.00, 'insurance_fee' => 4.70],
            ['insurance_id' => 5, 'amount_from' => 300.01, 'amount_to' => 400.00, 'insurance_fee' => 5.70],
            ['insurance_id' => 6, 'amount_from' => 400.01, 'amount_to' => 500.00, 'insurance_fee' => 6.70],
            ['insurance_id' => 7, 'amount_from' => 500.01, 'amount_to' => 600.00, 'insurance_fee' => 7.70],
        ];

        // Continue pattern: each $100 increment adds $1.00 to fee
        for ($id = 15; $id <= 58; $id++) {
            $offset = $id - 15;
            $from = 600.01 + ($offset * 100);
            $to = 700.00 + ($offset * 100);
            $fee = 8.70 + $offset;
            $rates[] = [
                'insurance_id' => $id,
                'amount_from' => $from,
                'amount_to' => $to,
                'insurance_fee' => $fee,
            ];
        }

        DB::table('insurance')->insertOrIgnore($rates);
    }
}
