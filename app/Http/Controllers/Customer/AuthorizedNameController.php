<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorizedNameRequest;
use App\Models\AuthorizedName;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorizedNameController extends Controller
{
    /**
     * Store a new authorized name for the authenticated customer.
     */
    public function store(StoreAuthorizedNameRequest $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validated();
        $data['customers_id'] = $customer->customers_id;

        $name = AuthorizedName::create($data);

        if ($name) {
            session()->flash('message', 'The authorized name has been saved.');
        } else {
            session()->flash('message', 'The authorized name could not be saved. Please, try again.');
        }

        return redirect(route('customer.account') . '#authorized-names');
    }

    /**
     * Show the edit form for an authorized name.
     */
    public function edit(int $id): View
    {
        $authorizedName = AuthorizedName::findOrFail($id);

        $this->authorize('update', $authorizedName);

        return view('customer.authorized-names.edit', compact('authorizedName'));
    }

    /**
     * Update an existing authorized name.
     */
    public function update(StoreAuthorizedNameRequest $request, int $id): RedirectResponse
    {
        $authorizedName = AuthorizedName::findOrFail($id);

        $this->authorize('update', $authorizedName);

        $customer = Auth::guard('customer')->user();
        $data = $request->validated();
        $data['customers_id'] = $customer->customers_id;

        if ($authorizedName->update($data)) {
            session()->flash('message', 'The authorized name has been saved.');
        } else {
            session()->flash('message', 'The authorized name could not be saved. Please, try again.');
        }

        return redirect(route('customer.account') . '#authorized-names');
    }

    /**
     * Delete an authorized name.
     */
    public function destroy(int $id): RedirectResponse
    {
        $authorizedName = AuthorizedName::findOrFail($id);

        $this->authorize('delete', $authorizedName);

        if ($authorizedName->delete()) {
            session()->flash('message', 'The authorized name has been deleted.');
        } else {
            session()->flash('message', 'The authorized name could not be deleted. Please, try again.');
        }

        return redirect(route('customer.account') . '#authorized-names');
    }
}
