<?php

namespace App\Models\OrderLineItems;

class OrderShipping extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_shipping';
    }

    protected static function lineItemTitle(): string
    {
        return 'Shipping:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 1;
    }
}
