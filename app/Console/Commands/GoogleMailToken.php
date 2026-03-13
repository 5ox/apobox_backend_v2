<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use League\OAuth2\Client\Provider\Google;

class GoogleMailToken extends Command
{
    protected $signature = 'mail:google-token
        {--client-id= : Google OAuth client ID (defaults to GOOGLE_CLIENT_ID env)}
        {--client-secret= : Google OAuth client secret (defaults to GOOGLE_CLIENT_SECRET env)}';

    protected $description = 'Generate a Google OAuth2 refresh token for Gmail SMTP (XOAUTH2)';

    public function handle(): int
    {
        $clientId = $this->option('client-id') ?: config('apobox.google_oauth.client_id');
        $clientSecret = $this->option('client-secret') ?: config('apobox.google_oauth.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $this->error('GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET must be set.');
            return self::FAILURE;
        }

        $provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => 'http://localhost',
            'accessType' => 'offline',
        ]);

        $authUrl = $provider->getAuthorizationUrl([
            'scope' => ['https://mail.google.com/'],
            'prompt' => 'consent',
            'access_type' => 'offline',
            'login_hint' => config('mail.mailers.gmail-oauth.username', 'admin@apobox.com'),
        ]);

        $this->newLine();
        $this->info('=== Google OAuth2 Setup for Gmail SMTP ===');
        $this->newLine();
        $this->line('1. Open this URL in your browser:');
        $this->newLine();
        $this->line("   {$authUrl}");
        $this->newLine();
        $this->line('2. Sign in with your Google Workspace account');
        $this->line('3. Grant the "Gmail" permission');
        $this->line('4. After redirect, copy the "code" parameter from the URL bar:');
        $this->line('   http://localhost/?code=XXXXXX&scope=...');
        $this->newLine();

        $code = $this->ask('Paste the authorization code here');

        if (empty($code)) {
            $this->error('No authorization code provided.');
            return self::FAILURE;
        }

        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to exchange code for token: ' . $e->getMessage());
            return self::FAILURE;
        }

        $refreshToken = $token->getRefreshToken();

        if (empty($refreshToken)) {
            $this->error('No refresh token returned. Try revoking app access at');
            $this->error('https://myaccount.google.com/permissions and run again.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Set this environment variable in Railway:');
        $this->newLine();
        $this->line("   GOOGLE_MAIL_REFRESH_TOKEN={$refreshToken}");
        $this->newLine();
        $this->warn('Keep this token secure — it provides Gmail access to the account.');

        if ($this->confirm('Test the refresh token now?', true)) {
            try {
                $accessToken = $provider->getAccessToken('refresh_token', [
                    'refresh_token' => $refreshToken,
                ]);
                $this->info('Token refresh OK. Expires: '
                    . date('Y-m-d H:i:s', $accessToken->getExpires()));
            } catch (\Exception $e) {
                $this->error('Token refresh failed: ' . $e->getMessage());
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
