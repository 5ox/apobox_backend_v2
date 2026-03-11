<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TrackingController extends Controller
{
    /**
     * Show the scan/tracking entry form.
     */
    public function add(): View
    {
        // TODO: Port from CakePHP
        return view('manager.tracking.add');
    }

    /**
     * Store a new tracking/scan entry.
     */
    public function store(StoreTrackingRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Search and list tracking/scan entries.
     */
    public function search(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.tracking.search');
    }

    /**
     * Show the edit form for a tracking entry.
     */
    public function edit(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.tracking.edit', compact('id'));
    }

    /**
     * Update an existing tracking entry.
     */
    public function update(StoreTrackingRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Delete a tracking entry.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }
}
