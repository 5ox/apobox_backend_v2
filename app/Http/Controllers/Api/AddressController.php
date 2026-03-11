<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Mail\Welcome;
use App\Models\Address;
use App\Models\Customer;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AddressController extends Controller
{
    /**
     * Store a new address via API.
     *
     * Ported from CakePHP AddressesController::_jsonAdd().
     * Creates a ShippingAddress (validated for APO/FPO/DPO format),
     * sets the customer's default addresses if not already set,
     * and sends a welcome email to new customers who just completed
     * their address setup.
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // The request must include a customers_id identifying who the address belongs to
        $customerId = $request->input('customers_id');

        if (empty($customerId)) {
            return response()->json([
                'error' => 'Customer ID is required.',
            ], 422);
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found.',
            ], 404);
        }

        // Default country to USA (223) if not set
        if (empty($validated['entry_country_id'])) {
            $validated['entry_country_id'] = 223;
        }

        $validated['customers_id'] = $customer->customers_id;

        try {
            DB::beginTransaction();

            $address = ShippingAddress::create($validated);

            // Set the customer's default addresses if they are not already set.
            // This handles the initial signup flow where the widget creates the
            // customer's first address.
            $needsUpdate = false;
            $updateData = [];

            if (empty($customer->customers_default_address_id)) {
                $updateData['customers_default_address_id'] = $address->address_book_id;
                $needsUpdate = true;
            }

            if (empty($customer->customers_shipping_address_id)) {
                $updateData['customers_shipping_address_id'] = $address->address_book_id;
                $needsUpdate = true;
            }

            if ($needsUpdate) {
                $customer->update($updateData);
                $customer->refresh();
            }

            DB::commit();

            // Send welcome email to new customers completing their signup
            $this->sendWelcomeEmailIfNeeded($customer);

            Log::info('API: Address created', [
                'address_book_id' => $address->address_book_id,
                'customers_id' => $customer->customers_id,
            ]);

            return response()->json([
                'data' => [
                    'type' => 'shipping_addresses',
                    'id' => $address->address_book_id,
                    'attributes' => $address->toArray(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('API: Address creation failed', [
                'customers_id' => $customerId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'The address could not be saved. ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Send a welcome email to a customer if they have just completed
     * their initial address setup.
     *
     * Ported from CakePHP AddressesController::_sendWelcomeEmail().
     * Checks that the customer has a default address with a zone
     * (indicating a complete APO-type address) before sending.
     */
    protected function sendWelcomeEmailIfNeeded(Customer $customer): void
    {
        // Load the default address to check if it is complete
        $customer->load('defaultAddress.zone');

        if (!$customer->defaultAddress) {
            return;
        }

        $defaultAddress = $customer->defaultAddress;

        try {
            $almostFinishedUrl = url('/customers/almost-finished');

            $addressData = [
                'entry_firstname' => $defaultAddress->entry_firstname,
                'entry_lastname' => $defaultAddress->entry_lastname,
                'entry_street_address' => $defaultAddress->entry_street_address,
                'entry_city' => $defaultAddress->entry_city,
                'entry_state' => $defaultAddress->zone?->zone_code ?? $defaultAddress->entry_state,
                'entry_postcode' => $defaultAddress->entry_postcode,
            ];

            Mail::to($customer->customers_email_address)
                ->queue(new Welcome(
                    $customer->customers_firstname,
                    $customer->customers_lastname,
                    $customer->billing_id,
                    $addressData,
                    $almostFinishedUrl
                ));
        } catch (\Exception $e) {
            // Log but don't fail the address creation if email fails
            Log::error('Failed to send welcome email', [
                'customers_id' => $customer->customers_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
