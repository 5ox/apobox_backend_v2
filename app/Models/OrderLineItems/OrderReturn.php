<?php

namespace App\Models\OrderLineItems;

class OrderReturn extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom_3';
    }

    protected static function lineItemTitle(): string
    {
        return 'Return Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
