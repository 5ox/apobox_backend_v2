<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomPackageRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomPackageRequestController extends Controller
{
    /**
     * List all custom package requests.
     */
    public function index(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.requests.index');
    }

    /**
     * Show the create form for a custom package request (admin context).
     */
    public function create(int $customerId): View
    {
        // TODO: Port from CakePHP
        return view('manager.requests.create', compact('customerId'));
    }

    /**
     * Store a new custom package request (admin context).
     */
    public function store(StoreCustomPackageRequest $request, int $customerId): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the edit form for a custom package request.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.requests.edit', compact('id'));
    }

    /**
     * Update an existing custom package request.
     */
    public function update(StoreCustomPackageRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete a custom package request.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
