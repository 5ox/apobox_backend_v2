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
     */
    public static function getFee(int $ounces): float
    {
        $schedule = config('apobox.fee_by_weight', []);

        // Schedule is expected as an array of [max_ounces => fee] sorted ascending.
        foreach ($schedule as $maxOz => $fee) {
            if ($ounces <= $maxOz) {
                return (float) $fee;
            }
        }

        // If weight exceeds all thresholds, return the last (highest) fee.
        return (float) end($schedule) ?: 0.00;
    }
}
