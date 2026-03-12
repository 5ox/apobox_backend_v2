<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\Log;
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
                'PayPal credentials not configured. '
                . 'client_id=' . (empty($this->clientId) ? 'EMPTY' : 'set(' . strlen($this->clientId) . ' chars)')
                . ', client_secret=' . (empty($this->clientSecret) ? 'EMPTY' : 'set(' . strlen($this->clientSecret) . ' chars)')
                . ', mode=' . $this->mode
                . '. Check PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET environment variables.'
            );
        }

        Log::channel('payment')->info('PayPal: initializing client', [
            'mode' => $this->mode,
            'client_id_len' => strlen($this->clientId),
        ]);

        $this->client = new PayPalClient;
        $this->client->setApiCredentials(config('paypal'));
        $token = $this->client->getAccessToken();

        if (empty($token)) {
            Log::channel('payment')->error('PayPal: getAccessToken returned empty', [
                'mode' => $this->mode,
            ]);
            throw new RuntimeException('PayPal: failed to obtain access token. Check credentials and mode.');
        }

        Log::channel('payment')->info('PayPal: access token obtained');
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
        Log::channel('payment')->info('PayPal: chargeCard called', [
            'vault_id' => substr($cardToken, 0, 8) . '...',
            'amount' => $amount,
            'description' => $description,
        ]);

        $this->ensureInitialized();

        try {
            $orderPayload = [
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
            ];

            $order = $this->client->createOrder($orderPayload);

            $status = $order['status'] ?? 'UNKNOWN';
            $paymentId = $order['id'] ?? null;

            Log::channel('payment')->info('PayPal: createOrder response', [
                'status' => $status,
                'payment_id' => $paymentId,
                'full_response' => $order,
            ]);

            if ($status !== 'COMPLETED') {
                Log::channel('payment')->warning('PayPal: order not COMPLETED', [
                    'status' => $status,
                    'response' => $order,
                ]);
            }

            return [
                'success' => $status === 'COMPLETED',
                'payment_id' => $paymentId,
                'state' => strtolower($status),
            ];
        } catch (Exception $e) {
            Log::channel('payment')->error('PayPal: chargeCard exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
