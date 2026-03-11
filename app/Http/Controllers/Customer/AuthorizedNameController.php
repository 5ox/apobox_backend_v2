<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorizedNameRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorizedNameController extends Controller
{
    /**
     * Store a new authorized name for the authenticated customer.
     */
    public function store(StoreAuthorizedNameRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }

    /**
     * Show the edit form for an authorized name.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('customer.authorized-names.edit', compact('id'));
    }

    /**
     * Update an existing authorized name.
     */
    public function update(StoreAuthorizedNameRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }

    /**
     * Delete an authorized name.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }
}
