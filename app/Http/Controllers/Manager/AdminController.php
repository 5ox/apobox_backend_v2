<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\Admin;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /**
     * List all admin users.
     */
    public function index(): View
    {
        $admins = Admin::orderBy('email')->paginate(25);

        return view('manager.admins.index', compact('admins'));
    }

    /**
     * Show the create admin form.
     */
    public function create(): View
    {
        return view('manager.admins.create');
    }

    /**
     * Store a new admin user.
     */
    public function store(StoreAdminRequest $request): RedirectResponse
    {
        Admin::create([
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'role' => $request->input('role', 'employee'),
        ]);

        session()->flash('message', 'The admin has been saved.');

        return redirect()->route(auth('admin')->user()->role . '.admins.index');
    }

    /**
     * Show the edit form for an admin user.
     */
    public function edit(int $id): View
    {
        $admin = Admin::findOrFail($id);

        return view('manager.admins.edit', compact('admin'));
    }

    /**
     * Update an existing admin user.
     */
    public function update(UpdateAdminRequest $request, int $id): RedirectResponse
    {
        $admin = Admin::findOrFail($id);

        $data = [
            'email' => $request->input('email'),
            'role' => $request->input('role'),
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->input('password'));
        }

        $admin->update($data);

        session()->flash('message', 'The admin has been saved.');

        return redirect()->route(auth('admin')->user()->role . '.admins.index');
    }

    /**
     * Delete an admin user.
     */
    public function destroy(int $id): RedirectResponse
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        session()->flash('message', 'The admin has been deleted.');

        return redirect()->route(auth('admin')->user()->role . '.admins.index');
    }
}
