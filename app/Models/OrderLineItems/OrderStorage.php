<?php

namespace App\Models\OrderLineItems;

class OrderStorage extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom';
    }

    protected static function lineItemTitle(): string
    {
        return 'Storage Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
