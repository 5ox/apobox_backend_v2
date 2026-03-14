<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsuranceTierController extends Controller
{
    /**
     * List all insurance tiers.
     */
    public function index(): View
    {
        $tiers = Insurance::orderBy('amount_from')->get();

        return view('manager.settings.insurance-tiers.index', compact('tiers'));
    }

    /**
     * Show the create tier form.
     */
    public function create(): View
    {
        return view('manager.settings.insurance-tiers.create');
    }

    /**
     * Store a new insurance tier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount_from' => ['required', 'numeric', 'min:0'],
            'amount_to' => ['required', 'numeric', 'min:0', 'gt:amount_from'],
            'insurance_fee' => ['required', 'numeric', 'min:0'],
        ]);

        Insurance::create($validated);

        session()->flash('message', 'Insurance tier has been created.');

        return redirect()->route(auth('admin')->user()->role . '.settings.insurance-tiers');
    }

    /**
     * Show the edit form for an insurance tier.
     */
    public function edit(int $id): View
    {
        $tier = Insurance::findOrFail($id);

        return view('manager.settings.insurance-tiers.edit', compact('tier'));
    }

    /**
     * Update an existing insurance tier.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'amount_from' => ['required', 'numeric', 'min:0'],
            'amount_to' => ['required', 'numeric', 'min:0', 'gt:amount_from'],
            'insurance_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $tier = Insurance::findOrFail($id);
        $tier->update($validated);

        session()->flash('message', 'Insurance tier has been updated.');

        return redirect()->route(auth('admin')->user()->role . '.settings.insurance-tiers');
    }

    /**
     * Delete an insurance tier.
     */
    public function destroy(int $id): RedirectResponse
    {
        $tier = Insurance::findOrFail($id);
        $tier->delete();

        session()->flash('message', 'Insurance tier has been deleted.');

        return redirect()->route(auth('admin')->user()->role . '.settings.insurance-tiers');
    }
}
