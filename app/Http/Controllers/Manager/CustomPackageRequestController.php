<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomPackageRequest;
use App\Models\CustomPackageRequest as PackageRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomPackageRequestController extends Controller
{
    /**
     * Package statuses used for filtering.
     */
    protected array $packageStatuses = [
        1 => 'New',
        2 => 'Processing',
        3 => 'Completed',
        4 => 'Cancelled',
    ];

    /**
     * List all custom package requests with search and filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $fromThePast = $request->input('from_the_past', config('apobox.search.date.default'));
        $showStatus = $request->input('showStatus');

        $query = PackageRequest::with(['customer', 'order.status'])
            ->orderBy('order_add_date', 'desc');

        if (!empty($search)) {
            $terms = explode(' ', $search);
            foreach ($terms as $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('billing_id', 'LIKE', '%' . $term . '%')
                      ->orWhere('orders_id', 'LIKE', '%' . $term . '%')
                      ->orWhere('tracking_id', 'LIKE', '%' . $term . '%');
                });
            }
        }

        if (!empty($fromThePast)) {
            $query->where('order_add_date', '>=', $fromThePast);
        }

        if (!empty($showStatus)) {
            $query->where('package_status', $showStatus);
        }

        $requests = $query->paginate(25);
        $statusFilterOptions = $this->packageStatuses;

        return view('manager.requests.index', compact(
            'requests',
            'search',
            'fromThePast',
            'statusFilterOptions',
            'showStatus'
        ));
    }

    /**
     * Show the create form for a custom package request (admin context).
     */
    public function create(int $customerId): View
    {
        $customer = Customer::where('customers_id', $customerId)->firstOrFail();
        $packageStatuses = $this->packageStatuses;

        return view('manager.requests.create', compact('customer', 'packageStatuses'));
    }

    /**
     * Store a new custom package request (admin context).
     */
    public function store(StoreCustomPackageRequest $request, int $customerId): RedirectResponse
    {
        $customer = Customer::where('customers_id', $customerId)->firstOrFail();

        PackageRequest::create(array_merge(
            $request->validated(),
            ['customers_id' => $customer->customers_id]
        ));

        session()->flash('message', 'The custom package request has been created.');

        return redirect()->route('manager.requests.index');
    }

    /**
     * Show the edit form for a custom package request.
     */
    public function edit(int $id): View
    {
        $packageRequest = PackageRequest::with('customer')
            ->where('custom_orders_id', $id)
            ->firstOrFail();

        $packageStatuses = $this->packageStatuses;

        return view('manager.requests.edit', compact('packageRequest', 'packageStatuses'));
    }

    /**
     * Update an existing custom package request.
     */
    public function update(StoreCustomPackageRequest $request, int $id): RedirectResponse
    {
        $packageRequest = PackageRequest::where('custom_orders_id', $id)->firstOrFail();

        $packageRequest->update($request->validated());

        session()->flash('message', 'Custom package request was successfully updated!');

        return redirect()->route('manager.requests.index');
    }

    /**
     * Delete a custom package request.
     */
    public function destroy(int $id): RedirectResponse
    {
        $packageRequest = PackageRequest::where('custom_orders_id', $id)->firstOrFail();

        // Cannot delete if associated with an order
        if (!empty($packageRequest->orders_id)) {
            session()->flash('message', "The custom package request could not be deleted because it's associated with an order.");
            return redirect()->route('manager.requests.index');
        }

        $packageRequest->delete();

        session()->flash('message', 'The custom package request was successfully deleted!');

        return redirect()->route('manager.requests.index');
    }
}
