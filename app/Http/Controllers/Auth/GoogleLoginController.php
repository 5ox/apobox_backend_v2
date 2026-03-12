<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Client\Provider\Google;

class GoogleLoginController extends Controller
{
    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect(): RedirectResponse
    {
        $provider = $this->getProvider();

        $authUrl = $provider->getAuthorizationUrl();
        session(['oauth2_state' => $provider->getState()]);

        return redirect()->away($authUrl);
    }

    /**
     * Handle Google OAuth callback.
     *
     * Matches the returned email against an existing Admin record. If found,
     * logs the admin in and redirects to their role-prefixed dashboard.
     */
    public function callback(): RedirectResponse
    {
        // User denied authorization or remote error
        if ($error = request()->query('error')) {
            return $this->googleError('Error: ' . $error);
        }

        $provider = $this->getProvider();

        // Validate the OAuth state to prevent CSRF
        if (request()->query('state') !== session('oauth2_state')) {
            return $this->googleError('Error: invalid authorization state.');
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => request()->query('code'),
            ]);
            $googleUser = $provider->getResourceOwner($token);
        } catch (\Exception $e) {
            return $this->googleError('Token Error: ' . $e->getMessage());
        }

        // Look up the admin by email
        $admin = Admin::where('email', $googleUser->getEmail())
            ->first();

        if ($admin) {
            Auth::guard('admin')->login($admin);
            session()->forget('oauth2_state');
            session()->flash('message', 'You have been logged in.');

            return redirect('/' . $admin->role);
        }

        if (config('apobox.google_oauth.log_failed_attempts')) {
            Log::warning('GoogleLoginController: Attempted Google login by ' . $googleUser->getEmail());
        }

        return $this->googleError("Your email address is not authorized or could not be found.");
    }

    /**
     * Build the Google OAuth2 provider instance.
     */
    protected function getProvider(): Google
    {
        return new Google([
            'clientId' => config('apobox.google_oauth.client_id'),
            'clientSecret' => config('apobox.google_oauth.client_secret'),
            'redirectUri' => config('apobox.google_oauth.redirect_uri'),
        ]);
    }

    /**
     * Flash an error, clear OAuth state and redirect to admin login.
     */
    protected function googleError(string $message): RedirectResponse
    {
        session()->flash('message', $message);
        session()->forget('oauth2_state');

        return redirect()->route('admin.login');
    }
}
