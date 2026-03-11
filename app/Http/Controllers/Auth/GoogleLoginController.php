<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class GoogleLoginController extends Controller
{
    /**
     * Redirect to Google OAuth.
     */
    public function redirect(): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Redirect to Google OAuth consent screen
        return redirect('/');
    }

    /**
     * Handle Google OAuth callback.
     */
    public function callback(): RedirectResponse
    {
        // TODO: Port from CakePHP
        // Handle OAuth callback, find/create admin, log in, redirect
        return redirect('/manager');
    }
}
