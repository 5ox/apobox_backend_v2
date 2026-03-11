<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Show the customer account dashboard.
     */
    public function account(): View
    {
        // TODO: Port from CakePHP
        return view('customer.account');
    }

    /**
     * Show edit form for a specific section of the customer profile.
     */
    public function editPartial(string $partial): View
    {
        // TODO: Port from CakePHP
        // Load partial edit form based on $partial (e.g., 'personal', 'contact', etc.)
        return view('customer.edit-partial', compact('partial'));
    }

    /**
     * Handle profile updates from partial edit forms.
     */
    public function update(UpdateCustomerRequest $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }

    /**
     * Show the change password form.
     */
    public function changePassword(): View
    {
        // TODO: Port from CakePHP
        return view('customer.change-password');
    }

    /**
     * Handle password update.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Validate old password, set new password
        return redirect()->route('customer.account');
    }

    /**
     * Show the account incomplete page.
     */
    public function accountIncomplete(): View
    {
        // TODO: Port from CakePHP
        return view('customer.account-incomplete');
    }

    /**
     * Show the almost finished page (post-registration).
     */
    public function almostFinished(): View
    {
        // TODO: Port from CakePHP
        return view('customer.almost-finished');
    }

    /**
     * Handle close account request (from email link).
     */
    public function closeAccount(string $hash): View|RedirectResponse
    {
        // TODO: Port from CakePHP
        // Verify hash, show confirmation page
        return view('customer.close-account', compact('hash'));
    }

    /**
     * Confirm and process account closure.
     */
    public function confirmClose(int $customerId, string $hash): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Verify hash matches customer, deactivate account
        return redirect('/');
    }
}
