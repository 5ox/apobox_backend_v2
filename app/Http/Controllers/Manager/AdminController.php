<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /**
     * List all admin users.
     */
    public function index(): View
    {
        // TODO: Port from CakePHP
        return view('manager.admins.index');
    }

    /**
     * Show the create admin form.
     */
    public function create(): View
    {
        // TODO: Port from CakePHP
        return view('manager.admins.create');
    }

    /**
     * Store a new admin user.
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the edit form for an admin user.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.admins.edit', compact('id'));
    }

    /**
     * Update an existing admin user.
     */
    public function update(UpdateAdminRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete an admin user.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
