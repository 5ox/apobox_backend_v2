<?php

namespace App\Models\OrderLineItems;

class OrderRepack extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom_1';
    }

    protected static function lineItemTitle(): string
    {
        return 'Repack Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
