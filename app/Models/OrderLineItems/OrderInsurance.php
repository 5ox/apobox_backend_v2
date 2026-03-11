<?php

namespace App\Models\OrderLineItems;

class OrderInsurance extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_insurance';
    }

    protected static function lineItemTitle(): string
    {
        return 'Insurance:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 2;
    }
}
