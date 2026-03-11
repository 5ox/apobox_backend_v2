<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAffiliateLinkRequest;
use App\Http\Requests\UpdateAffiliateLinkRequest;
use App\Models\AffiliateLink;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AffiliateLinkController extends Controller
{
    /**
     * List all affiliate links.
     */
    public function index(): View
    {
        $affiliateLinks = AffiliateLink::orderBy('created', 'desc')->paginate(25);

        return view('manager.affiliate-links.index', compact('affiliateLinks'));
    }

    /**
     * Show the create affiliate link form.
     */
    public function create(): View
    {
        return view('manager.affiliate-links.create');
    }

    /**
     * Store a new affiliate link.
     */
    public function store(StoreAffiliateLinkRequest $request): RedirectResponse
    {
        AffiliateLink::create($request->validated());

        session()->flash('message', 'The affiliate link has been saved.');

        return redirect()->route('manager.affiliate-links.index');
    }

    /**
     * Show the edit form for an affiliate link.
     */
    public function edit(int $id): View
    {
        $affiliateLink = AffiliateLink::findOrFail($id);

        return view('manager.affiliate-links.edit', compact('affiliateLink'));
    }

    /**
     * Update an existing affiliate link.
     */
    public function update(UpdateAffiliateLinkRequest $request, int $id): RedirectResponse
    {
        $affiliateLink = AffiliateLink::findOrFail($id);
        $affiliateLink->update($request->validated());

        session()->flash('message', 'The affiliate link has been saved.');

        return redirect()->route('manager.affiliate-links.index');
    }

    /**
     * Delete an affiliate link.
     */
    public function destroy(int $id): RedirectResponse
    {
        $affiliateLink = AffiliateLink::findOrFail($id);
        $affiliateLink->delete();

        session()->flash('message', 'The affiliate link has been deleted.');

        return redirect()->route('manager.affiliate-links.index');
    }
}
