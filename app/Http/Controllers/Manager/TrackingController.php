<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTrackingRequest;
use App\Models\Tracking;
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
        return view('manager.tracking.add');
    }

    /**
     * Store a new tracking/scan entry.
     */
    public function store(StoreTrackingRequest $request): RedirectResponse
    {
        $prefix = auth('admin')->user()->role;
        $trackingNumber = $request->input('tracking_number');

        if (empty($trackingNumber)) {
            return redirect()->route($prefix . '.tracking.add');
        }

        // Check for duplicates
        if (Tracking::where('tracking_id', $trackingNumber)->exists()) {
            session()->flash('message', 'Tracking ID exists.');
            return redirect()->route($prefix . '.tracking.add');
        }

        $data = [
            'tracking_id' => $trackingNumber,
            'warehouse' => config('apobox.warehouse.code', 'APO'),
        ];

        // Only include comments/notes if provided
        if ($request->filled('notes')) {
            $data['comments'] = $request->input('notes', '');
        }

        Tracking::create($data);

        session()->flash('message', 'Tracking ID has been saved.');

        return redirect()->route($prefix . '.tracking.add');
    }

    /**
     * Search and list tracking/scan entries.
     */
    public function search(Request $request): View
    {
        $search = $request->input('q');
        $fromThePast = $request->input('from_the_past', config('apobox.search.date.default', '-6 months'));

        $query = Tracking::query();

        if (!empty($search)) {
            $terms = explode(' ', $search);
            foreach ($terms as $term) {
                $query->where('tracking_id', 'LIKE', '%' . $term . '%');
            }
        }

        if (!empty($fromThePast) && $fromThePast !== 'all') {
            $fromDate = date_create($fromThePast);
            if ($fromDate) {
                $query->where('timestamp', '>=', $fromDate->format('Y-m-d H:i:s'));
            }
        }

        $results = $query->orderBy('timestamp', 'desc')->paginate(25);

        $userIsManager = in_array(auth('admin')->user()?->role, ['manager', 'sysadmin']);

        return view('manager.tracking.search', compact(
            'search',
            'results',
            'fromThePast',
            'userIsManager'
        ));
    }

    /**
     * Show the edit form for a tracking entry.
     */
    public function edit(string $id): View
    {
        $tracking = Tracking::findOrFail($id);

        return view('manager.tracking.edit', compact('tracking'));
    }

    /**
     * Update an existing tracking entry.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $tracking = Tracking::findOrFail($id);

        $tracking->update([
            'comments' => $request->input('comments', $request->input('notes', '')),
        ]);

        session()->flash('message', 'The scan has been updated.');

        return redirect()->route(auth('admin')->user()->role . '.tracking.search');
    }

    /**
     * Delete a tracking entry.
     */
    public function destroy(string $id): RedirectResponse
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->delete();

        session()->flash('message', 'The scan has been deleted.');

        return redirect()->route(auth('admin')->user()->role . '.tracking.search');
    }
}
