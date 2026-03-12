<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.admin.login');
    }

    /**
     * Handle an admin login request.
     *
     * Legacy password-based login, only enabled when google_oauth.legacy_login
     * is true in config/apobox.php.
     */
    public function login(Request $request): RedirectResponse
    {
        if (! config('apobox.google_oauth.legacy_login')) {
            session()->flash('message', 'Password login is disabled. Please use Google login.');
            return redirect()->route('admin.login');
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();
            session()->flash('message', 'You have been logged in.');

            return redirect('/' . $admin->role);
        }

        session()->flash('message', 'Your email address or password was incorrect.');

        return redirect()->back()->withInput($request->only('email'));
    }

    /**
     * Log the admin out.
     */
    public function logout(): RedirectResponse
    {
        Auth::guard('admin')->logout();
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('message', 'You have been logged out.');

        return redirect()->route('admin.login');
    }
}
