<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AffiliateLinkController extends Controller
{
    /**
     * List all affiliate links.
     */
    public function index(): View
    {
        // TODO: Port from CakePHP
        return view('manager.affiliate-links.index');
    }

    /**
     * Show the create affiliate link form.
     */
    public function create(): View
    {
        // TODO: Port from CakePHP
        return view('manager.affiliate-links.create');
    }

    /**
     * Store a new affiliate link.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the edit form for an affiliate link.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.affiliate-links.edit', compact('id'));
    }

    /**
     * Update an existing affiliate link.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete an affiliate link.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
