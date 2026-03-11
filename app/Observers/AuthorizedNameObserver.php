<?php

namespace App\Observers;

use App\Models\AuthorizedName;

class AuthorizedNameObserver
{
    /**
     * Handle the AuthorizedName "saved" event.
     *
     * Update the parent customer's search index so authorized names
     * are included in search results.
     */
    public function saved(AuthorizedName $authorizedName): void
    {
        $this->refreshCustomerSearchIndex($authorizedName);
    }

    /**
     * Handle the AuthorizedName "deleted" event.
     *
     * Update the parent customer's search index to remove the
     * deleted authorized name.
     */
    public function deleted(AuthorizedName $authorizedName): void
    {
        $this->refreshCustomerSearchIndex($authorizedName);
    }

    /**
     * Refresh the search index for the parent customer.
     */
    protected function refreshCustomerSearchIndex(AuthorizedName $authorizedName): void
    {
        $customer = $authorizedName->customer;

        if ($customer) {
            $customer->updateSearchIndex();
        }
    }
}
