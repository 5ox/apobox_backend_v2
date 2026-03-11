<?php

namespace App\Models\OrderLineItems;

class OrderBattery extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom_2';
    }

    protected static function lineItemTitle(): string
    {
        return 'Battery Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
