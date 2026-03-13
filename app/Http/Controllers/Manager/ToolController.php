<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use League\OAuth2\Client\Provider\Google;

class ToolController extends Controller
{
    /**
     * Available commands that can be run from the web UI.
     */
    protected array $commands = [
        'apply-storage-fees' => [
            'command' => 'app:apply-storage-fees',
            'label' => 'Apply Storage Fees',
            'description' => 'Accrue daily storage fees on warehouse orders past grace period and auto-charge customers.',
            'icon' => 'warehouse',
            'confirm' => 'This will charge customers with overdue storage. Run dry-run first?',
            'options' => ['dry-run'],
        ],
        'apply-storage-fees-dry' => [
            'command' => 'app:apply-storage-fees',
            'args' => ['--dry-run' => true],
            'label' => 'Storage Fees (Dry Run)',
            'description' => 'Preview storage fee calculations without making any changes.',
            'icon' => 'eye',
            'confirm' => null,
        ],
        'customer-reminders-awaiting' => [
            'command' => 'app:customer-reminders',
            'args' => ['--awaiting-payment' => true],
            'label' => 'Send Payment Reminders',
            'description' => 'Send reminder emails to customers with orders awaiting payment.',
            'icon' => 'mail',
            'confirm' => 'This will send emails to customers. Continue?',
        ],
    ];

    /**
     * Show the tools page with available commands.
     */
    public function index(): View
    {
        return view('manager.tools.index', [
            'commands' => $this->commands,
        ]);
    }

    /**
     * Run a registered artisan command and show the output.
     */
    public function run(Request $request, string $command): RedirectResponse
    {
        if (!isset($this->commands[$command])) {
            session()->flash('message', 'Unknown command.');
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        $config = $this->commands[$command];
        $args = $config['args'] ?? [];

        Artisan::call($config['command'], $args);
        $output = Artisan::output();

        session()->flash('message', 'Command completed.');
        session()->flash('tool_output', $output);

        return redirect()->route(auth('admin')->user()->role . '.tools.index');
    }

    /**
     * Start Gmail OAuth flow to obtain a refresh token.
     */
    public function gmailOAuthStart(): RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $prefix = auth('admin')->user()->routePrefix();
        $callbackUrl = url("/{$prefix}/tools/gmail-oauth/callback");

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
            'login_hint' => config('mail.mailers.gmail-oauth.username', 'admin@apobox.com'),
        ]);

        session(['gmail_oauth_state' => $provider->getState()]);

        return redirect()->away($authUrl);
    }

    /**
     * Handle Gmail OAuth callback, display the refresh token.
     */
    public function gmailOAuthCallback(Request $request): View|RedirectResponse
    {
        abort_unless(auth('admin')->user()->isSysadmin(), 403);

        $prefix = auth('admin')->user()->routePrefix();

        if ($error = $request->query('error')) {
            session()->flash('message', 'Gmail OAuth error: ' . $error);
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        if ($request->query('state') !== session('gmail_oauth_state')) {
            session()->flash('message', 'Gmail OAuth error: invalid state.');
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        $callbackUrl = url("/{$prefix}/tools/gmail-oauth/callback");

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
            session()->flash('message', 'Gmail OAuth token error: ' . $e->getMessage());
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        $refreshToken = $token->getRefreshToken();
        session()->forget('gmail_oauth_state');

        if (empty($refreshToken)) {
            session()->flash('message', 'No refresh token returned. Try revoking app access at https://myaccount.google.com/permissions and try again.');
            return redirect()->route(auth('admin')->user()->role . '.tools.index');
        }

        Log::info('Gmail OAuth: refresh token obtained by admin ' . auth('admin')->id());

        session()->flash('message', 'Gmail OAuth connected successfully.');
        session()->flash('gmail_refresh_token', $refreshToken);

        return redirect()->route(auth('admin')->user()->role . '.tools.index');
    }
}
