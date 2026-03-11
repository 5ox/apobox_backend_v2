<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomPackageRequest;
use App\Models\CustomPackageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomPackageRequestController extends Controller
{
    /**
     * List the authenticated customer's custom package requests.
     */
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $requests = CustomPackageRequest::where('customers_id', $customer->customers_id)
            ->with('order.status')
            ->orderByDesc('request_date')
            ->paginate(20);

        return view('customer.requests.index', compact('requests'));
    }

    /**
     * Show the create form for a new custom package request.
     */
    public function create(): View
    {
        return view('customer.requests.create');
    }

    /**
     * Store a new custom package request.
     */
    public function store(StoreCustomPackageRequest $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validated();
        $data['customers_id'] = $customer->customers_id;
        $data['status'] = 'pending';
        $data['request_date'] = now();

        $packageRequest = CustomPackageRequest::create($data);

        if ($packageRequest) {
            session()->flash('message', 'Your custom package request has been created.');
            return redirect()->route('customer.account');
        }

        session()->flash('message', 'The custom package request could not be saved. Please, try again.');

        return redirect()->back()->withInput();
    }

    /**
     * Show the edit form for a custom package request.
     */
    public function edit(int $id): View
    {
        $packageRequest = CustomPackageRequest::with('customer')->findOrFail($id);

        $this->authorize('update', $packageRequest);

        // Customers can only edit instructions if the request is linked to an order
        $allowedFields = $this->getAllowedFields($packageRequest);

        return view('customer.requests.edit', compact('packageRequest', 'allowedFields'));
    }

    /**
     * Update an existing custom package request.
     */
    public function update(StoreCustomPackageRequest $request, int $id): RedirectResponse
    {
        $packageRequest = CustomPackageRequest::findOrFail($id);

        $this->authorize('update', $packageRequest);

        $allowedFields = $this->getAllowedFields($packageRequest);
        $data = $request->only($allowedFields);

        if ($packageRequest->update($data)) {
            session()->flash('message', 'Custom package request was successfully updated!');
            return redirect()->route('customer.account');
        }

        session()->flash('message', 'Custom package request could not be updated.');

        return redirect()->back()->withInput();
    }

    /**
     * Delete a custom package request.
     *
     * Cannot delete a request that is already associated with an order.
     */
    public function destroy(int $id): RedirectResponse
    {
        $packageRequest = CustomPackageRequest::findOrFail($id);

        $this->authorize('delete', $packageRequest);

        // Cannot delete if linked to an order
        if (! empty($packageRequest->orders_id)) {
            session()->flash('message', "The custom package request could not be deleted because it's associated with an order.");
            return redirect()->route('customer.account');
        }

        if ($packageRequest->delete()) {
            session()->flash('message', 'The custom package request was successfully deleted!');
        } else {
            session()->flash('message', 'The custom package request could not be deleted.');
        }

        return redirect()->route('customer.account');
    }

    /**
     * Determine which fields a customer is allowed to edit.
     *
     * If the request is linked to an order, only instructions can be changed.
     */
    protected function getAllowedFields(CustomPackageRequest $packageRequest): array
    {
        // If linked to an order, customers can only edit instructions
        if (! empty($packageRequest->orders_id)) {
            return ['instructions'];
        }

        return [
            'description',
            'instructions',
        ];
    }
}
