<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Mail\BlankMessage;
use App\Models\Customer;
use App\Services\PaymentService;
use App\Services\CreditCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{
    /**
     * API fields that are safe to return in JSON responses.
     * Mirrors the CakePHP dataForApi() filter.
     */
    protected const API_FIELDS = [
        'customers_id',
        'customers_firstname',
        'customers_lastname',
        'customers_email_address',
        'billing_id',
    ];

    /**
     * Show a customer by ID.
     *
     * Ported from CakePHP CustomersController::api_view().
     * Returns customer data with their default address.
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::with(['defaultAddress.zone'])
            ->find($id);

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'type' => 'customers',
                'id' => $customer->customers_id,
                'attributes' => $customer->only(self::API_FIELDS),
                'relationships' => [
                    'default_address' => $customer->defaultAddress,
                ],
            ],
        ]);
    }

    /**
     * Create a new customer via API.
     *
     * Ported from CakePHP CustomersController::add().
     * Creates the customer record, generates a billing ID, and returns
     * the new customer in JSON API format.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Observer handles hashing via CustomerObserver::saving()

            // Generate a unique billing ID from customer initials + random digits
            $validated['billing_id'] = Customer::newBillingId(
                $validated['customers_firstname'],
                $validated['customers_lastname']
            );

            $validated['is_active'] = true;

            $customer = Customer::create($validated);

            Log::info('API: Customer registered', [
                'customers_id' => $customer->customers_id,
                'billing_id' => $customer->billing_id,
            ]);

            return response()->json([
                'data' => [
                    'type' => 'customers',
                    'id' => $customer->customers_id,
                    'attributes' => $customer->only(self::API_FIELDS),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Customer creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'The customer could not be created.',
            ], 500);
        }
    }

    /**
     * Authenticate a customer via API and return their data.
     *
     * Ported from CakePHP CustomersController::_jsonLogin().
     * Validates email/password credentials and returns customer data
     * on success. Does not issue tokens (Sanctum tokens are for Admin
     * API users; this endpoint verifies customer credentials for
     * external systems like the widget).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'customers_email_address' => ['required', 'email'],
            'customers_password' => ['required', 'string'],
        ]);

        $customer = Customer::where('customers_email_address', $request->input('customers_email_address'))
            ->where('is_active', true)
            ->first();

        if (!$customer) {
            return response()->json([
                'error' => 'Your email address or password was incorrect.',
            ], 400);
        }

        // Use the custom ApoboxPasswordHasher (supports legacy MD5+salt and bcrypt)
        if (!Hash::check($request->input('customers_password'), $customer->customers_password)) {
            return response()->json([
                'error' => 'Your email address or password was incorrect.',
            ], 400);
        }

        // Rehash to bcrypt if still using legacy MD5+salt format
        if (Hash::needsRehash($customer->customers_password)) {
            $customer->customers_password = $request->input('customers_password');
            $customer->save();
        }

        Log::info('API: Customer login', ['customers_id' => $customer->customers_id]);

        return response()->json([
            'data' => [
                'type' => 'customers',
                'id' => $customer->customers_id,
                'attributes' => $customer->only(self::API_FIELDS),
            ],
        ]);
    }

    /**
     * Send a notification email to a customer.
     *
     * Ported from CakePHP CustomersController::api_notify().
     * Accepts a message body and optional subject, then sends a
     * blank-template email to the customer. Returns 204 on success.
     */
    public function notify(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        $request->validate([
            'message.body' => ['required', 'string'],
            'message.subject' => ['nullable', 'string'],
        ]);

        $body = $request->input('message.body');
        $subject = $request->input('message.subject', config('apobox.email.subjects.customer', 'APO Box - Customer Notification'));

        $customerName = $customer->customers_firstname . ' ' . $customer->customers_lastname;

        try {
            Mail::to($customer->customers_email_address)
                ->queue(new BlankMessage($customerName, $body, $subject));

            Log::info('API: Notification sent to customer', [
                'customers_id' => $id,
                'subject' => $subject,
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('API: Failed to send customer notification', [
                'customers_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'There was a problem sending the message.',
            ], 400);
        }
    }

    /**
     * Charge a customer's credit card.
     *
     * Ported from CakePHP PaymentComponent::charge().
     * Accepts either a stored card token or raw card details,
     * processes the charge through the PaymentService, and returns
     * the result.
     */
    public function charge(Request $request): JsonResponse
    {
        $request->validate([
            'customers_id' => ['required', 'integer', 'exists:customers,customers_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::find($request->input('customers_id'));

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        $amount = (float) $request->input('amount');
        $description = $request->input('description', 'APO Box Shipping');

        // Retrieve stored card token from customer
        $cardToken = $customer->card_token;

        if (empty($cardToken)) {
            // If no stored token, try to decrypt and use stored card data
            $creditCardService = app(CreditCardService::class);
            $decryptedNumber = $customer->decryptCreditCard();

            if (empty($decryptedNumber)) {
                return response()->json([
                    'error' => 'Customer does not have a valid payment method on file.',
                ], 422);
            }

            // Store the card in PayPal vault first
            $paymentService = app(PaymentService::class);
            $cardType = $creditCardService->getCardType($decryptedNumber);

            $cardToken = $paymentService->storeCard([
                'type' => $cardType ?? 'visa',
                'number' => $decryptedNumber,
                'expire_month' => $customer->cc_expires_month,
                'expire_year' => $customer->cc_expires_year,
                'cvv' => $customer->cc_cvv,
                'first_name' => $customer->cc_firstname,
                'last_name' => $customer->cc_lastname,
            ]);

            if (!$cardToken) {
                return response()->json([
                    'error' => 'Unable to process the customer\'s payment method.',
                ], 422);
            }

            // Save the token for future charges
            $customer->card_token = $cardToken;
            $customer->save();
        }

        try {
            $paymentService = app(PaymentService::class);
            $result = $paymentService->chargeCard($cardToken, $amount, $description);

            if (!$result['success']) {
                Log::warning('API: Payment charge failed', [
                    'customers_id' => $customer->customers_id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return response()->json([
                    'error' => $result['error'] ?? 'The charge could not be processed.',
                ], 400);
            }

            Log::info('API: Payment charge successful', [
                'customers_id' => $customer->customers_id,
                'payment_id' => $result['payment_id'],
                'amount' => $amount,
            ]);

            return response()->json([
                'data' => [
                    'type' => 'payments',
                    'id' => $result['payment_id'],
                    'attributes' => [
                        'state' => $result['state'],
                        'amount' => $amount,
                        'customers_id' => $customer->customers_id,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('API: Payment charge exception', [
                'customers_id' => $customer->customers_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred while processing the payment.',
            ], 500);
        }
    }
}
