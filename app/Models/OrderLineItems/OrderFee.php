<?php

namespace App\Models\OrderLineItems;

class OrderFee extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_fee';
    }

    protected static function lineItemTitle(): string
    {
        return 'Handling Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 4;
    }

    /**
     * Look up the handling fee for a given weight in ounces
     * using the fee schedule defined in config('apobox.fee_by_weight').
     *
     * Config keys are minimum ounces, sorted ascending.
     * e.g. [0 => 10.95, 17 => 12.95] means 0–16 oz = $10.95, 17+ oz = $12.95.
     */
    public static function getFee(int $ounces): float
    {
        $schedule = config('apobox.fee_by_weight', []);
        $fee = 0.00;

        foreach ($schedule as $minOz => $amount) {
            if ($ounces >= $minOz) {
                $fee = (float) $amount;
            }
        }

        return $fee;
    }
}
