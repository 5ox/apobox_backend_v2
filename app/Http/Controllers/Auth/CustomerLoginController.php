<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Mail\Welcome;
use App\Models\Customer;
use App\Models\CustomersInfo;
use App\Models\PasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerLoginController extends Controller
{
    /**
     * Show the customer login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.customer.login');
    }

    /**
     * Handle a customer login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'customers_email_address' => ['required', 'email'],
            'customers_password' => ['required', 'string'],
        ]);

        $customer = Customer::where('customers_email_address', $request->input('customers_email_address'))
            ->where('is_active', 1)
            ->first();

        if ($customer && Hash::check($request->input('customers_password'), $customer->customers_password)) {
            Auth::guard('customer')->login($customer);

            // Record login activity
            $info = CustomersInfo::firstOrCreate(
                ['customers_info_id' => $customer->customers_id],
            );
            $info->update([
                'customers_info_date_of_last_logon' => now(),
                'customers_info_number_of_logons' => ($info->customers_info_number_of_logons ?? 0) + 1,
            ]);

            session()->flash('message', 'You have been logged in!');

            return redirect()->intended(route('customer.account'));
        }

        session()->flash('message', 'Your email address or password was incorrect.');

        return redirect()->back()->withInput($request->only('customers_email_address'));
    }

    /**
     * Log the customer out.
     */
    public function logout(): RedirectResponse
    {
        Auth::guard('customer')->logout();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('message', 'You have been logged out.');

        return redirect()->route('login');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('auth.customer.forgot-password');
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = Customer::where('customers_email_address', $request->input('email'))
            ->where('is_active', 1)
            ->first();

        if (! $customer) {
            session()->flash('message', 'A customer with the email address you entered could not be found.');
            return redirect()->back();
        }

        $passwordRequest = PasswordRequest::create([
            'customer_id' => $customer->customers_id,
        ]);

        if (! $passwordRequest) {
            session()->flash('message', 'We were unable to create a password reset request for you. Please try again.');
            return redirect()->back();
        }

        $resetUrl = url('/reset-password/' . $passwordRequest->id);

        try {
            Mail::to($customer->customers_email_address)
                ->send(new ForgotPassword($customer->full_name, $resetUrl));
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            $passwordRequest->delete();
            session()->flash('message', 'There was a problem sending your password reset email.');
            return redirect()->back();
        }

        session()->flash('message', 'An email with instructions on how to reset your password has been sent.');

        return redirect()->route('login');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(string $uuid): View
    {
        // Clean up expired requests
        PasswordRequest::deleteExpired();

        $passwordRequest = PasswordRequest::valid()->findOrFail($uuid);

        return view('auth.customer.reset-password', compact('uuid'));
    }

    /**
     * Handle a reset password request.
     */
    public function resetPassword(Request $request, string $uuid): RedirectResponse
    {
        PasswordRequest::deleteExpired();

        $passwordRequest = PasswordRequest::valid()->find($uuid);

        if (! $passwordRequest) {
            abort(404, 'The password request could not be found or is no longer valid.');
        }

        $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
            'password_confirm' => ['required', 'string'],
        ]);

        if ($request->input('new_password') !== $request->input('password_confirm')) {
            session()->flash('message', 'Password and password confirm did not match. Please try again.');
            return redirect()->back();
        }

        $customer = Customer::findOrFail($passwordRequest->customer_id);
        $customer->customers_password = Hash::make($request->input('new_password'));
        $customer->save();

        // Delete the password request after use
        $passwordRequest->delete();

        // Log the user in
        Auth::guard('customer')->login($customer);

        session()->flash('message', 'Your password has been changed and you have been logged in.');

        return redirect()->route('customer.account');
    }

    /**
     * Handle customer registration (JSON API for widget).
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'customers_firstname' => ['required', 'string', 'max:64'],
            'customers_lastname' => ['required', 'string', 'max:64'],
            'customers_email_address' => ['required', 'email', 'max:96', 'unique:customers,customers_email_address'],
            'customers_password' => ['required', 'string', 'min:6'],
        ]);

        $customer = Customer::create([
            'customers_firstname' => $request->input('customers_firstname'),
            'customers_lastname' => $request->input('customers_lastname'),
            'customers_email_address' => $request->input('customers_email_address'),
            'customers_password' => Hash::make($request->input('customers_password')),
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($customer);

        // Record registration activity
        CustomersInfo::create([
            'customers_info_id' => $customer->customers_id,
            'customers_info_date_account_created' => now(),
        ]);

        session()->flash('message', 'Your account has been created!');

        return redirect()->route('customer.account');
    }
}
