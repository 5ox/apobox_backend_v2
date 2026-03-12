<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorizedNameRequest;
use App\Models\AuthorizedName;
use App\Models\Customer;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorizedNameController extends Controller
{
    /**
     * Store a new authorized name for a customer (admin context).
     */
    public function store(StoreAuthorizedNameRequest $request, int $customerId): RedirectResponse
    {
        $customer = Customer::where('customers_id', $customerId)->firstOrFail();

        AuthorizedName::create(array_merge(
            $request->validated(),
            ['customers_id' => $customer->customers_id]
        ));

        session()->flash('message', 'The authorized name has been saved.');

        return redirect()->route(auth('admin')->user()->role . '.customers.view', $customer->customers_id);
    }

    /**
     * Show the edit form for an authorized name.
     */
    public function edit(int $id): View
    {
        $authorizedName = AuthorizedName::where('authorized_names_id', $id)->firstOrFail();

        return view('manager.authorized-names.edit', compact('authorizedName'));
    }

    /**
     * Update an existing authorized name.
     */
    public function update(StoreAuthorizedNameRequest $request, int $id): RedirectResponse
    {
        $authorizedName = AuthorizedName::where('authorized_names_id', $id)->firstOrFail();

        $authorizedName->update($request->validated());

        session()->flash('message', 'The authorized name has been saved.');

        return redirect()->route(auth('admin')->user()->role . '.customers.view', $authorizedName->customers_id);
    }

    /**
     * Delete an authorized name.
     */
    public function destroy(int $id): RedirectResponse
    {
        $authorizedName = AuthorizedName::where('authorized_names_id', $id)->firstOrFail();
        $customerId = $authorizedName->customers_id;

        $authorizedName->delete();

        session()->flash('message', 'The authorized name has been deleted.');

        return redirect()->route(auth('admin')->user()->role . '.customers.view', $customerId);
    }
}
