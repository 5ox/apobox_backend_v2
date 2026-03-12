<?php

namespace App\Observers;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    /**
     * Handle the Customer "creating" event.
     *
     * Generate a billing_id if one has not been set.
     */
    public function creating(Customer $customer): void
    {
        if (empty($customer->billing_id)
            && ! empty($customer->customers_firstname)
            && ! empty($customer->customers_lastname)
        ) {
            $customer->billing_id = Customer::newBillingId(
                $customer->customers_firstname,
                $customer->customers_lastname
            );
        }
    }

    /**
     * Handle the Customer "saving" event.
     *
     * Hash the password if it has been changed, using the 'apobox' hasher.
     */
    public function saving(Customer $customer): void
    {
        if ($customer->isDirty('customers_password') && ! empty($customer->customers_password)) {
            $customer->customers_password = Hash::driver('apobox')->make(
                $customer->customers_password
            );
        }
    }

    /**
     * Handle the Customer "saved" event.
     *
     * CC authorization is handled by the controller that has raw card data.
     * The observer cannot re-authorize because the model only stores encrypted data.
     */
    public function saved(Customer $customer): void
    {
        // CC authorization intentionally removed — raw card data is not available
        // on the model. Authorization happens in the controller payment flow.
    }
}
