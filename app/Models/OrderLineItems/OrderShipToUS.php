<?php

namespace App\Models\OrderLineItems;

class OrderShipToUS extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom_5';
    }

    protected static function lineItemTitle(): string
    {
        return 'Ship to US Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
