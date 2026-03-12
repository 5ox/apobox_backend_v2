<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;
use RuntimeException;

class PaymentService
{
    protected ?PayPalClient $client = null;
    protected string $clientId;
    protected string $clientSecret;
    protected string $mode;
    protected bool $initialized = false;

    public function __construct(string $clientId, string $clientSecret, string $mode = 'sandbox')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->mode = $mode;
    }

    /**
     * Lazily initialize the PayPal client on first use.
     */
    protected function ensureInitialized(): void
    {
        if ($this->initialized) {
            return;
        }

        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new RuntimeException(
                'PayPal credentials not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables.'
            );
        }

        $this->client = new PayPalClient;
        $this->client->setApiCredentials([
            'mode' => $this->mode,
            $this->mode => [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => '',
            'locale' => 'en_US',
            'validate_ssl' => true,
        ]);
        $this->client->getAccessToken();
        $this->initialized = true;
    }

    /**
     * Check if PayPal credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Store a credit card in PayPal vault.
     */
    public function storeCard(array $cardData): ?string
    {
        $this->ensureInitialized();

        try {
            $expiry = '20' . $cardData['expire_year'] . '-' . str_pad($cardData['expire_month'], 2, '0', STR_PAD_LEFT);

            $setupToken = $this->client->createPaymentSetupToken([
                'payment_source' => [
                    'card' => [
                        'number' => $cardData['number'],
                        'expiry' => $expiry,
                        'name' => trim(($cardData['first_name'] ?? '') . ' ' . ($cardData['last_name'] ?? '')),
                        'security_code' => $cardData['cvv'],
                    ],
                ],
            ]);

            if (empty($setupToken['id'])) {
                report(new Exception('PayPal setup token creation failed: ' . json_encode($setupToken)));
                return null;
            }

            $paymentToken = $this->client->createPaymentSourceToken([
                'payment_source' => [
                    'token' => [
                        'id' => $setupToken['id'],
                        'type' => 'SETUP_TOKEN',
                    ],
                ],
            ]);

            return $paymentToken['id'] ?? null;
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Authorize a credit card (vault it for validation).
     */
    public function authorizeCard(array $cardData): ?string
    {
        return $this->storeCard($cardData);
    }

    /**
     * Charge a stored card by vault token.
     */
    public function chargeCard(string $cardToken, float $amount, string $description = ''): array
    {
        $this->ensureInitialized();

        try {
            $order = $this->client->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                        'description' => $description ?: 'APO Box Shipping',
                    ],
                ],
                'payment_source' => [
                    'card' => [
                        'vault_id' => $cardToken,
                    ],
                ],
            ]);

            $status = $order['status'] ?? 'UNKNOWN';
            $paymentId = $order['id'] ?? null;

            return [
                'success' => $status === 'COMPLETED',
                'payment_id' => $paymentId,
                'state' => strtolower($status),
            ];
        } catch (Exception $e) {
            report($e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
