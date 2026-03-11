<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomPackageRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomPackageRequestController extends Controller
{
    /**
     * List the authenticated customer's custom package requests.
     */
    public function index(): View
    {
        // TODO: Port from CakePHP
        return view('customer.requests.index');
    }

    /**
     * Show the create form for a new custom package request.
     */
    public function create(): View
    {
        // TODO: Port from CakePHP
        return view('customer.requests.create');
    }

    /**
     * Store a new custom package request.
     */
    public function store(StoreCustomPackageRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect('/requests');
    }

    /**
     * Show the edit form for a custom package request.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('customer.requests.edit', compact('id'));
    }

    /**
     * Update an existing custom package request.
     */
    public function update(StoreCustomPackageRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect('/requests');
    }

    /**
     * Delete a custom package request.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect('/requests');
    }
}
