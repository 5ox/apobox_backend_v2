<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    /**
     * Show the create address form.
     */
    public function create(): View
    {
        $zones = Zone::pluck('zone_name', 'zone_id');

        return view('customer.addresses.create', compact('zones'));
    }

    /**
     * Store a new address for the authenticated customer.
     *
     * Optionally, the form can include a 'make_this_my' field to assign
     * the new address as one of the customer's default addresses:
     * billing, shipping, or emergency.
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validated();
        $data['customers_id'] = $customer->customers_id;

        $address = Address::create($data);

        if (! $address) {
            session()->flash('message', 'The address could not be saved. Please, try again.');
            return redirect()->back()->withInput();
        }

        $message = 'The address has been saved.';

        // Check if the user wants to set this as one of their defaults
        $makeThisMy = $request->input('make_this_my');
        if (! empty($makeThisMy)) {
            $fieldMap = [
                'billing' => 'customers_default_address_id',
                'shipping' => 'customers_shipping_address_id',
                'emergency' => 'customers_emergency_address_id',
            ];

            if (isset($fieldMap[$makeThisMy])) {
                $updated = $customer->update([
                    $fieldMap[$makeThisMy] => $address->address_book_id,
                ]);

                if ($updated) {
                    $message = 'The address has been saved and set as your ' . $makeThisMy . ' address.';
                } else {
                    session()->flash('message', 'We were unable to set the new address as your ' . $makeThisMy . ' address.');
                    return redirect('/customers/edit/addresses');
                }
            }
        }

        session()->flash('message', $message);

        return redirect()->route('customer.account');
    }

    /**
     * Show the edit form for an address.
     */
    public function edit(int $id): View
    {
        $address = Address::findOrFail($id);

        $this->authorize('update', $address);

        $zones = Zone::pluck('zone_name', 'zone_id');
        $addressName = $address->full;
        $addressId = $address->address_book_id;

        return view('customer.addresses.edit', compact('address', 'zones', 'addressName', 'addressId'));
    }

    /**
     * Update an existing address.
     */
    public function update(UpdateAddressRequest $request, int $id): RedirectResponse
    {
        $address = Address::findOrFail($id);

        $this->authorize('update', $address);

        $customer = Auth::guard('customer')->user();
        $data = $request->validated();
        $data['customers_id'] = $customer->customers_id;

        if ($address->update($data)) {
            session()->flash('message', 'Your address was successfully updated!');
            return redirect()->route('customer.account');
        }

        session()->flash('message', 'The address could not be updated.');

        return redirect()->back()->withInput();
    }

    /**
     * Delete an address.
     *
     * Prevents deletion if the address is currently in use as one of
     * the customer's default addresses.
     */
    public function destroy(int $id): RedirectResponse
    {
        $address = Address::findOrFail($id);

        $this->authorize('delete', $address);

        $customer = Auth::guard('customer')->user();

        // Check if the address is in use
        $inUse = (
            $customer->customers_default_address_id == $id
            || $customer->customers_shipping_address_id == $id
            || $customer->customers_emergency_address_id == $id
        );

        if ($inUse) {
            session()->flash('message', 'You are currently using that address as one of your default addresses. Please select another address to use in its place and then try again.');
            return redirect('/customers/edit/addresses');
        }

        if ($address->delete()) {
            session()->flash('message', 'The address was successfully deleted!');
        } else {
            session()->flash('message', 'The address could not be deleted.');
        }

        return redirect()->route('customer.account');
    }
}
