<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\AuthorizedName;

class AuthorizedNamePolicy
{
    public function update(Customer $customer, AuthorizedName $name): bool
    {
        return $customer->customers_id === $name->customers_id;
    }

    public function delete(Customer $customer, AuthorizedName $name): bool
    {
        return $customer->customers_id === $name->customers_id;
    }
}
