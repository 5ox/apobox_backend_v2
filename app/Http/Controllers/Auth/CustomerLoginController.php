<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerLoginController extends Controller
{
    /**
     * Show the customer login form.
     */
    public function showLoginForm(): View
    {
        // TODO: Port from CakePHP
        return view('auth.customer.login');
    }

    /**
     * Handle a customer login request.
     */
    public function login(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Validate credentials, authenticate, redirect to account
        return redirect()->route('customer.account');
    }

    /**
     * Log the customer out.
     */
    public function logout(): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('login');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword(): View
    {
        // TODO: Port from CakePHP
        return view('auth.customer.forgot-password');
    }

    /**
     * Handle a forgot password request.
     */
    public function forgotPassword(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Validate email, send reset link
        return redirect()->back();
    }

    /**
     * Show the reset password form.
     */
    public function showResetPassword(string $uuid): View
    {
        // TODO: Port from CakePHP
        return view('auth.customer.reset-password', compact('uuid'));
    }

    /**
     * Handle a reset password request.
     */
    public function resetPassword(Request $request, string $uuid): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Validate new password, update, redirect to login
        return redirect()->route('login');
    }

    /**
     * Handle customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('customer.account');
    }
}
