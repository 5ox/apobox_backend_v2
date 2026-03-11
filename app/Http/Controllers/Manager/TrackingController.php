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
        $trackingId = $request->input('tracking_id');

        if (empty($trackingId)) {
            return redirect()->route('manager.tracking.add');
        }

        // Check for duplicates
        if (Tracking::where('tracking_id', $trackingId)->exists()) {
            session()->flash('message', 'Tracking ID exists.');
            return redirect()->route('manager.tracking.add');
        }

        $data = [
            'tracking_id' => $trackingId,
            'warehouse' => config('apobox.warehouse.code', 'APO'),
        ];

        // Only include comments if the exception checkbox was checked
        if ($request->filled('add_exception') && $request->input('add_exception')) {
            $data['comments'] = $request->input('comments', '');
        }

        Tracking::create($data);

        session()->flash('message', 'Tracking ID has been saved.');

        return redirect()->route('manager.tracking.add');
    }

    /**
     * Search and list tracking/scan entries.
     */
    public function search(Request $request): View
    {
        $search = $request->input('q');
        $fromThePast = $request->input('from_the_past', config('apobox.search.date.default'));

        $query = Tracking::query();

        if (!empty($search)) {
            $terms = explode(' ', $search);
            foreach ($terms as $term) {
                $query->where('tracking_id', 'LIKE', '%' . $term . '%');
            }
        }

        if (!empty($fromThePast)) {
            $query->where('timestamp', '>=', $fromThePast);
        }

        $results = $query->orderBy('timestamp', 'desc')->paginate(25);

        $userIsManager = auth('admin')->user()?->role === 'manager';

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
    public function edit(int $id): View
    {
        $tracking = Tracking::findOrFail($id);

        return view('manager.tracking.edit', compact('tracking'));
    }

    /**
     * Update an existing tracking entry.
     */
    public function update(StoreTrackingRequest $request, int $id): RedirectResponse
    {
        $tracking = Tracking::findOrFail($id);

        $tracking->update([
            'comments' => $request->input('comments', ''),
        ]);

        session()->flash('message', 'The scan has been updated.');

        return redirect()->route('manager.tracking.search');
    }

    /**
     * Delete a tracking entry.
     */
    public function destroy(int $id): RedirectResponse
    {
        $tracking = Tracking::findOrFail($id);
        $tracking->delete();

        session()->flash('message', 'The scan has been deleted.');

        return redirect()->route('manager.tracking.search');
    }
}
