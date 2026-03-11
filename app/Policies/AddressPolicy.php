<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Address;

class AddressPolicy
{
    public function update(Customer $customer, Address $address): bool
    {
        return $customer->customers_id === $address->customers_id;
    }

    public function delete(Customer $customer, Address $address): bool
    {
        return $customer->customers_id === $address->customers_id;
    }
}
