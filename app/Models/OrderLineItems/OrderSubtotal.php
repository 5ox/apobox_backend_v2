<?php

namespace App\Models\OrderLineItems;

class OrderSubtotal extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_subtotal';
    }

    protected static function lineItemTitle(): string
    {
        return 'Sub-Total:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 5;
    }

    /**
     * Calculate the subtotal by summing all line items that are NOT
     * the subtotal or total rows for the given order.
     */
    public function calculateTotal(): float
    {
        $excludeClasses = ['ot_subtotal', 'ot_total'];

        $sum = $this->newQueryWithoutScopes()
            ->where('orders_id', $this->orders_id)
            ->whereNotIn('class', $excludeClasses)
            ->sum('value');

        $this->value = $sum;
        $this->text = '$' . number_format((float) $sum, 2);
        $this->save();

        return (float) $sum;
    }
}
