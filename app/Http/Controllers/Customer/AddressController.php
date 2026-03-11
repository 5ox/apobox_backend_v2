<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AddressController extends Controller
{
    /**
     * Show the create address form.
     */
    public function create(): View
    {
        // TODO: Port from CakePHP
        return view('customer.addresses.create');
    }

    /**
     * Store a new address for the authenticated customer.
     */
    public function store(StoreAddressRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }

    /**
     * Show the edit form for an address.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('customer.addresses.edit', compact('id'));
    }

    /**
     * Update an existing address.
     */
    public function update(UpdateAddressRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }

    /**
     * Delete an address.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }
}
