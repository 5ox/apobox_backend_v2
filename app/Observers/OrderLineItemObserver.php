<?php

namespace App\Observers;

use App\Models\OrderLineItems\OrderLineItem;
use App\Models\OrderLineItems\OrderSubtotal;
use App\Models\OrderLineItems\OrderTotal;

class OrderLineItemObserver
{
    /**
     * Handle the OrderLineItem "saved" event.
     *
     * Recalculate the order subtotal and total whenever a line item
     * changes, unless the saved item itself IS the subtotal or total
     * (to avoid infinite recursion).
     */
    public function saved(OrderLineItem $item): void
    {
        $class = $item->getAttribute('class');

        // Skip recalculation if the saved item is the subtotal or total row
        if (in_array($class, ['ot_subtotal', 'ot_total'])) {
            return;
        }

        $this->recalculateOrderTotals($item->orders_id);
    }

    /**
     * Recalculate subtotal and total for the given order.
     */
    protected function recalculateOrderTotals(int $orderId): void
    {
        // Update or create the subtotal row
        $subtotal = OrderSubtotal::firstOrCreate(
            ['orders_id' => $orderId],
            ['value' => 0]
        );
        $subtotal->calculateTotal();

        // Update or create the total row
        $total = OrderTotal::firstOrCreate(
            ['orders_id' => $orderId],
            ['value' => 0]
        );
        $total->updateTotal();
    }
}
