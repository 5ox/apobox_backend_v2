<?php

namespace App\Services;

use PayPal\Api\CreditCard as PayPalCreditCard;
use PayPal\Api\CreditCardToken;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Exception;

class PaymentService
{
    protected ApiContext $apiContext;

    public function __construct(string $clientId, string $clientSecret, string $mode = 'sandbox')
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $clientSecret)
        );
        $this->apiContext->setConfig(['mode' => $mode, 'log.LogEnabled' => false]);
    }

    /**
     * Store a credit card in PayPal vault.
     */
    public function storeCard(array $cardData): ?string
    {
        try {
            $card = new PayPalCreditCard();
            $card->setType($cardData['type'] ?? 'visa')
                ->setNumber($cardData['number'])
                ->setExpireMonth($cardData['expire_month'])
                ->setExpireYear('20' . $cardData['expire_year'])
                ->setCvv2($cardData['cvv'])
                ->setFirstName($cardData['first_name'] ?? '')
                ->setLastName($cardData['last_name'] ?? '');

            $card->create($this->apiContext);

            return $card->getId();
        } catch (Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Authorize a credit card (zero-dollar auth for validation).
     */
    public function authorizeCard(array $cardData): ?string
    {
        return $this->storeCard($cardData);
    }

    /**
     * Charge a stored card by token.
     */
    public function chargeCard(string $cardToken, float $amount, string $description = ''): array
    {
        try {
            $creditCardToken = new CreditCardToken();
            $creditCardToken->setCreditCardId($cardToken);

            $fundingInstrument = new FundingInstrument();
            $fundingInstrument->setCreditCardToken($creditCardToken);

            $payer = new Payer();
            $payer->setPaymentMethod('credit_card')
                ->setFundingInstruments([$fundingInstrument]);

            $payAmount = new Amount();
            $payAmount->setCurrency('USD')
                ->setTotal(number_format($amount, 2, '.', ''));

            $transaction = new Transaction();
            $transaction->setAmount($payAmount)
                ->setDescription($description ?: 'APO Box Shipping');

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction]);

            $payment->create($this->apiContext);

            return [
                'success' => true,
                'payment_id' => $payment->getId(),
                'state' => $payment->getState(),
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
