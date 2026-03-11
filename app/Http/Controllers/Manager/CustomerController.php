<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentInfoRequest;
use App\Http\Requests\UpdateContactInfoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Search and list customers.
     */
    public function search(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.search');
    }

    /**
     * View a customer's details by internal ID.
     */
    public function view(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.view', compact('id'));
    }

    /**
     * View a customer's details by billing ID (e.g., AB1234).
     */
    public function viewByBillingId(string $billingId): View|RedirectResponse
    {
        // TODO: Port from CakePHP
        // Look up customer by billing ID, redirect to view
        return view('manager.customers.view', compact('billingId'));
    }

    /**
     * Show recent orders for a customer.
     */
    public function recentOrders(int $id): View|JsonResponse
    {
        // TODO: Port from CakePHP
        return view('manager.customers.recent-orders', compact('id'));
    }

    /**
     * Show the payment info edit form for a customer.
     */
    public function editPaymentInfo(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.edit-payment-info', compact('id'));
    }

    /**
     * Update a customer's payment info.
     */
    public function updatePaymentInfo(UpdatePaymentInfoRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the contact info edit form for a customer.
     */
    public function editContactInfo(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.edit-contact-info', compact('id'));
    }

    /**
     * Update a customer's contact info.
     */
    public function updateContactInfo(UpdateContactInfoRequest $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show all addresses for a customer.
     */
    public function addresses(int $id): View|JsonResponse
    {
        // TODO: Port from CakePHP
        return view('manager.customers.addresses', compact('id'));
    }

    /**
     * Show shipping addresses for a customer (JSON for AJAX).
     */
    public function shippingAddresses(int $id): JsonResponse
    {
        // TODO: Port from CakePHP
        return response()->json([]);
    }

    /**
     * Show the default addresses edit form for a customer.
     */
    public function editDefaultAddresses(int $id): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.edit-default-addresses', compact('id'));
    }

    /**
     * Update a customer's default addresses.
     */
    public function updateDefaultAddresses(Request $request, int $id): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the quick order form.
     */
    public function quickOrder(Request $request): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.quick-order');
    }

    /**
     * Process a quick order submission.
     */
    public function processQuickOrder(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Close a customer account (admin action).
     */
    public function closeAccount(int $customerId): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->back();
    }

    /**
     * Show the demographics report.
     */
    public function demographicsReport(): View
    {
        // TODO: Port from CakePHP
        return view('manager.customers.demographics');
    }
}
