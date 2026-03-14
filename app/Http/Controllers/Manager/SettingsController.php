<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use League\OAuth2\Client\Provider\Google;

class SettingsController extends Controller
{
    protected const MAIL_KEYS = [
        'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
        'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
    ];

    public function index(): View
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $mailSettings = Setting::getMany(self::MAIL_KEYS);

        $defaults = [
            'mail_mailer' => config('mail.default', 'gmail-oauth'),
            'mail_host' => config('mail.mailers.smtp.host', 'smtp.gmail.com'),
            'mail_port' => config('mail.mailers.smtp.port', 587),
            'mail_username' => config('mail.mailers.smtp.username') ?: config('mail.mailers.gmail-oauth.username', ''),
            'mail_password' => '',
            'mail_encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'mail_from_address' => config('mail.from.address', 'no-reply@apobox.com'),
            'mail_from_name' => config('mail.from.name', 'APO Box'),
        ];

        foreach ($defaults as $key => $default) {
            if ($key === 'mail_password') {
                $mailSettings[$key] = '';
                $mailSettings['mail_password_set'] = !empty(Setting::get('mail_password'));
            } else {
                $mailSettings[$key] = $mailSettings[$key] ?? $default;
            }
        }

        $oauthConfigured = !empty(config('mail.mailers.gmail-oauth.refresh_token'));

        return view('manager.settings.index', [
            'mailSettings' => $mailSettings,
            'oauthConfigured' => $oauthConfigured,
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $validated = $request->validate([
            'mail_mailer' => 'required|in:gmail-oauth,smtp',
            'mail_host' => 'required_if:mail_mailer,smtp|nullable|string|max:255',
            'mail_port' => 'required_if:mail_mailer,smtp|nullable|integer|min:1|max:65535',
            'mail_username' => 'required|email|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required_if:mail_mailer,smtp|nullable|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        Setting::setMany($validated);

        $prefix = auth('admin')->user()->routePrefix();
        return redirect("/{$prefix}/settings")->with('success', 'Mail settings saved.');
    }

    public function sendTestEmail(): RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $admin = auth('admin')->user();
        $prefix = $admin->routePrefix();

        try {
            Mail::raw(
                'This is a test email from APO Box Account. If you received this, your mail configuration is working correctly.',
                function ($message) use ($admin) {
                    $message->to($admin->email)->subject('APO Box - Test Email');
                }
            );

            return redirect("/{$prefix}/settings")->with('success', 'Test email sent to ' . $admin->email);
        } catch (\Exception $e) {
            return redirect("/{$prefix}/settings")->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function gmailOAuthStart(): RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $prefix = auth('admin')->user()->routePrefix();
        $callbackUrl = url("/{$prefix}/settings/gmail-oauth/callback");

        $provider = new Google([
            'clientId' => config('apobox.google_oauth.client_id'),
            'clientSecret' => config('apobox.google_oauth.client_secret'),
            'redirectUri' => $callbackUrl,
            'accessType' => 'offline',
        ]);

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['https://mail.google.com/'],
            'prompt' => 'consent',
            'access_type' => 'offline',
            'login_hint' => Setting::get('mail_username', config('mail.mailers.gmail-oauth.username', 'admin@apobox.com')),
        ]);

        session(['gmail_oauth_state' => $provider->getState()]);

        return redirect()->away($authUrl);
    }

    public function gmailOAuthCallback(Request $request): RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $prefix = auth('admin')->user()->routePrefix();
        $redirectRoute = "/{$prefix}/settings";

        if ($error = $request->query('error')) {
            return redirect($redirectRoute)->with('error', 'Gmail OAuth error: ' . $error);
        }

        if ($request->query('state') !== session('gmail_oauth_state')) {
            return redirect($redirectRoute)->with('error', 'Gmail OAuth error: invalid state.');
        }

        $callbackUrl = url("/{$prefix}/settings/gmail-oauth/callback");

        $provider = new Google([
            'clientId' => config('apobox.google_oauth.client_id'),
            'clientSecret' => config('apobox.google_oauth.client_secret'),
            'redirectUri' => $callbackUrl,
            'accessType' => 'offline',
        ]);

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $request->query('code'),
            ]);
        } catch (\Exception $e) {
            return redirect($redirectRoute)->with('error', 'Gmail OAuth token error: ' . $e->getMessage());
        }

        $refreshToken = $token->getRefreshToken();
        session()->forget('gmail_oauth_state');

        if (empty($refreshToken)) {
            return redirect($redirectRoute)->with('error', 'No refresh token returned. Try revoking app access at https://myaccount.google.com/permissions and try again.');
        }

        Log::info('Gmail OAuth: refresh token obtained by admin ' . auth('admin')->id());

        return redirect($redirectRoute)
            ->with('success', 'Gmail OAuth connected successfully.')
            ->with('gmail_refresh_token', $refreshToken);
    }
}
