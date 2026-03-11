<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(): View
    {
        // TODO: Port from CakePHP
        return view('auth.admin.login');
    }

    /**
     * Handle an admin login request.
     */
    public function login(Request $request): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Validate credentials, authenticate admin, redirect to dashboard
        return redirect('/manager');
    }

    /**
     * Log the admin out.
     */
    public function logout(): RedirectResponse
    {
        // TODO: Port from CakePHP
        return redirect()->route('admin.login');
    }
}
