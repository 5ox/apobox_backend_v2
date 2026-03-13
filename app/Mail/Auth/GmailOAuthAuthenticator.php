<?php

namespace App\Mail\Auth;

use League\OAuth2\Client\Provider\Google;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\Auth\AuthenticatorInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * XOAUTH2 authenticator for Gmail SMTP using league/oauth2-google.
 *
 * Refreshes the access token automatically via the stored refresh token,
 * caching it for 50 minutes (Google tokens last 60 minutes).
 */
class GmailOAuthAuthenticator implements AuthenticatorInterface
{
    private string $clientId;
    private string $clientSecret;
    private string $refreshToken;
    private string $username;

    public function __construct(
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        string $username
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;
        $this->username = $username;
    }

    public function getAuthKeyword(): string
    {
        return 'XOAUTH2';
    }

    public function authenticate(EsmtpTransport $client): void
    {
        try {
            $this->doAuthenticate($client);
        } catch (TransportException $e) {
            // Token may have just expired — evict cache and retry once
            Cache::forget('gmail_oauth_token:' . $this->username);
            $this->doAuthenticate($client);
        }
    }

    private function doAuthenticate(EsmtpTransport $client): void
    {
        $accessToken = $this->getAccessToken();

        // XOAUTH2 SASL initial response
        // https://developers.google.com/workspace/gmail/imap/xoauth2-protocol
        $authString = base64_encode(
            "user={$this->username}\1auth=Bearer {$accessToken}\1\1"
        );

        $client->executeCommand("AUTH XOAUTH2 {$authString}\r\n", [235]);
    }

    private function getAccessToken(): string
    {
        return Cache::remember(
            'gmail_oauth_token:' . $this->username,
            50 * 60,
            fn () => $this->refreshAccessToken()
        );
    }

    private function refreshAccessToken(): string
    {
        $provider = new Google([
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
        ]);

        $token = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $this->refreshToken,
        ]);

        Log::info('Gmail OAuth: refreshed access token', [
            'username' => $this->username,
            'expires' => date('Y-m-d H:i:s', $token->getExpires()),
        ]);

        return $token->getToken();
    }
}
