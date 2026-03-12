<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    /**
     * Store a new address for a customer (admin context).
     */
    public function store(StoreAddressRequest $request, int $customerId): RedirectResponse
    {
        $customer = Customer::where('customers_id', $customerId)->firstOrFail();

        Address::create(array_merge(
            $request->validated(),
            ['customers_id' => $customer->customers_id]
        ));

        session()->flash('message', 'The address has been saved.');

        return redirect()->route(auth('admin')->user()->role . '.customers.view', $customer->customers_id);
    }
}
