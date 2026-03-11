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
     * If credit card fields have changed, attempt to authorize the card
     * via PayPal (or configured gateway).
     */
    public function saved(Customer $customer): void
    {
        $ccFields = ['cc_number', 'cc_number_encrypted', 'cc_expires_month', 'cc_expires_year'];
        $ccDirty = collect($ccFields)->some(fn ($field) => $customer->wasChanged($field));

        if ($ccDirty && ! empty($customer->cc_number_encrypted)) {
            try {
                app(\App\Services\PayPalService::class)->authorizeCard($customer);
            } catch (\Throwable $e) {
                Log::error('CC authorization failed for customer ' . $customer->customers_id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
