<?php

namespace App\Models\OrderLineItems;

class OrderMisaddressed extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_custom_4';
    }

    protected static function lineItemTitle(): string
    {
        return 'Misaddressed Fee:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 3;
    }
}
