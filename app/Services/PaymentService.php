<?php

namespace App\Services;

use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Exception;

class PaymentService
{
    protected PayPalClient $client;

    public function __construct(string $clientId, string $clientSecret, string $mode = 'sandbox')
    {
        $this->client = new PayPalClient;
        $this->client->setApiCredentials([
            'mode' => $mode,
            $mode => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
            'payment_action' => 'Sale',
            'currency' => 'USD',
            'notify_url' => '',
            'locale' => 'en_US',
            'validate_ssl' => true,
        ]);
        $this->client->getAccessToken();
    }

    /**
     * Store a credit card in PayPal vault.
     *
     * Creates a setup token then converts it to a permanent payment token.
     * Returns the payment token ID (vault ID) for future charges.
     */
    public function storeCard(array $cardData): ?string
    {
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
