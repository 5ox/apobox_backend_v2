<?php

namespace App\Models\OrderLineItems;

class OrderTotal extends OrderLineItem
{
    protected static function lineItemClass(): string
    {
        return 'ot_total';
    }

    protected static function lineItemTitle(): string
    {
        return 'Total:';
    }

    protected static function lineItemSortOrder(): int
    {
        return 6;
    }

    /**
     * Override the text formatting to wrap in bold tags.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::creating(function ($item) {
            if ($item->value !== null) {
                $item->text = '<b>$' . number_format((float) $item->value, 2) . '</b>';
            }
        });

        static::saving(function ($item) {
            if ($item->value !== null && $item->isDirty('value')) {
                $item->text = '<b>$' . number_format((float) $item->value, 2) . '</b>';
            }
        });
    }

    /**
     * Recalculate the total from the subtotal row (which itself is the
     * sum of all non-subtotal, non-total line items).
     */
    public function updateTotal(): float
    {
        // The total equals the subtotal value (taxes/discounts can be
        // added here in the future).
        $subtotalValue = $this->newQueryWithoutScopes()
            ->where('orders_id', $this->orders_id)
            ->where('class', 'ot_subtotal')
            ->value('value') ?? 0;

        $total = (float) $subtotalValue;

        $this->value = $total;
        $this->text = '<b>$' . number_format($total, 2) . '</b>';
        $this->save();

        return $total;
    }
}
