<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAuthorizedNameRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthorizedNameController extends Controller
{
    /**
     * Store a new authorized name for a customer (admin context).
     */
    public function store(StoreAuthorizedNameRequest $request, int $customerId): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the edit form for an authorized name.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.authorized-names.edit', compact('id'));
    }

    /**
     * Update an existing authorized name.
     */
    public function update(StoreAuthorizedNameRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete an authorized name.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
