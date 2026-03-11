<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Order;

class OrderPolicy
{
    public function view(Customer $customer, Order $order): bool
    {
        return $customer->customers_id === $order->customers_id;
    }

    public function pay(Customer $customer, Order $order): bool
    {
        return $customer->customers_id === $order->customers_id
            && $order->orders_status === 2; // Awaiting Payment
    }
}
