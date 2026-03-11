<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\CustomPackageRequest;

class CustomPackageRequestPolicy
{
    public function view(Customer $customer, CustomPackageRequest $request): bool
    {
        return $customer->customers_id === $request->customers_id;
    }

    public function update(Customer $customer, CustomPackageRequest $request): bool
    {
        return $customer->customers_id === $request->customers_id;
    }

    public function delete(Customer $customer, CustomPackageRequest $request): bool
    {
        return $customer->customers_id === $request->customers_id;
    }
}
