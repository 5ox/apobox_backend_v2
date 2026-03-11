<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CustomersInfo;
use App\Models\CustomerReminder;
use App\Models\Order;
use App\Models\Zone;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * List the authenticated customer's orders.
     */
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = Order::where('customers_id', $customer->customers_id)
            ->with(['status', 'customer', 'total'])
            ->orderByDesc('date_purchased')
            ->paginate(20);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Show a single order belonging to the authenticated customer.
     */
    public function show(int $id): View
    {
        $order = Order::with([
            'customer',
            'status',
            'statusHistory.status',
            'lineItems',
            'total',
            'subtotal',
        ])->findOrFail($id);

        $this->authorize('view', $order);

        $orderCharges = $order->lineItems;

        return view('customer.orders.show', compact('order', 'orderCharges'));
    }

    /**
     * Show the manual payment form for an order.
     *
     * Only orders with status 2 (Awaiting Payment) can be paid manually.
     */
    public function payManually(int $id): View
    {
        $order = Order::with('total')->findOrFail($id);

        $this->authorize('pay', $order);

        $customer = Auth::guard('customer')->user();

        $addresses = Address::where('customers_id', $customer->customers_id)
            ->get()
            ->pluck('full', 'address_book_id');

        $zones = Zone::pluck('zone_name', 'zone_id');

        $selected = $customer->customers_default_address_id;

        $orderCharges = $order->lineItems()->get();

        return view('customer.orders.pay', compact(
            'order',
            'addresses',
            'zones',
            'selected',
            'orderCharges'
        ));
    }

    /**
     * Process manual payment for an order.
     *
     * Validates the credit card data, charges the card via PaymentService,
     * and updates the order status to Paid (4) on success. Optionally saves
     * the card to the customer's account.
     */
    public function processPayment(Request $request, int $id): RedirectResponse
    {
        $order = Order::with('total')->findOrFail($id);

        $this->authorize('pay', $order);

        $request->validate([
            'cc_firstname' => ['required', 'string', 'max:64'],
            'cc_lastname' => ['required', 'string', 'max:64'],
            'cc_number' => ['required', 'string', 'max:20'],
            'cc_expires_month' => ['required', 'string', 'max:2'],
            'cc_expires_year' => ['required', 'string', 'max:2'],
            'cc_cvv' => ['required', 'string', 'max:4'],
            'customers_default_address_id' => ['required'],
            'save' => ['nullable', 'boolean'],
        ]);

        $customer = Auth::guard('customer')->user();

        // Resolve the billing address
        $addressId = $request->input('customers_default_address_id');

        if ($addressId === 'custom') {
            // Use custom address fields from the form
            $request->validate([
                'entry_firstname' => ['required', 'string', 'max:64'],
                'entry_lastname' => ['required', 'string', 'max:64'],
                'entry_street_address' => ['required', 'string', 'max:255'],
                'entry_city' => ['required', 'string', 'max:64'],
                'entry_postcode' => ['required', 'string', 'max:10'],
                'entry_zone_id' => ['required', 'integer'],
                'entry_country_id' => ['required', 'integer'],
            ]);

            $billingAddress = [
                'entry_firstname' => $request->input('entry_firstname'),
                'entry_lastname' => $request->input('entry_lastname'),
                'entry_company' => $request->input('entry_company', ''),
                'entry_street_address' => $request->input('entry_street_address'),
                'entry_suburb' => $request->input('entry_suburb', ''),
                'entry_city' => $request->input('entry_city'),
                'entry_postcode' => $request->input('entry_postcode'),
                'entry_zone_id' => $request->input('entry_zone_id'),
                'entry_country_id' => $request->input('entry_country_id'),
            ];

            $zone = Zone::find($request->input('entry_zone_id'));
            $billingState = $zone ? $zone->zone_code : '';
        } else {
            $address = Address::with('zone', 'country')->findOrFail($addressId);

            // Verify the address belongs to the customer
            if ($address->customers_id !== $customer->customers_id) {
                abort(403);
            }

            $billingAddress = $address->toArray();
            $billingState = $address->zone ? $address->zone->zone_code : '';
        }

        // Attempt to charge the card
        $total = $order->total ? $order->total->value : 0;

        try {
            $paymentService = app(PaymentService::class);
            $result = $paymentService->chargeCard(
                $customer->card_token ?? '',
                (float) $total,
                'Order #' . $order->orders_id
            );

            if (empty($result['success'])) {
                session()->flash('message', 'The credit card you provided could not be charged. Please double check all information and try again.');
                return redirect()->back()->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Payment failed for order #' . $order->orders_id . ': ' . $e->getMessage());
            session()->flash('message', 'The credit card you provided could not be charged. Please double check all information and try again.');
            return redirect()->back()->withInput();
        }

        // Payment successful - update the order
        $billingName = ($billingAddress['entry_firstname'] ?? '') . ' ' . ($billingAddress['entry_lastname'] ?? '');

        $order->update([
            'orders_status' => 4,        // Paid
            'billing_status' => true,
            'cc_number' => $request->input('cc_number'),
            'cc_expires' => $request->input('cc_expires_month') . $request->input('cc_expires_year'),
            'cc_owner' => $request->input('cc_firstname') . ' ' . $request->input('cc_lastname'),
            'billing_name' => $billingName,
            'billing_company' => $billingAddress['entry_company'] ?? $billingName,
            'billing_street_address' => $billingAddress['entry_street_address'] ?? '',
            'billing_suburb' => $billingAddress['entry_suburb'] ?? '',
            'billing_city' => $billingAddress['entry_city'] ?? '',
            'billing_postcode' => $billingAddress['entry_postcode'] ?? '',
            'billing_state' => $billingState,
            'billing_country' => $billingAddress['entry_country'] ?? 'United States',
        ]);

        // Clear the awaiting payment reminder
        CustomerReminder::where('customers_id', $customer->customers_id)
            ->where('type', CustomerReminder::TYPE_AWAITING_PAYMENT)
            ->delete();

        $message = 'Your package has been paid for.';

        // Check if the customer wants to save the card to their profile
        if ($request->boolean('save')) {
            $ccEncrypted = $customer->encryptCreditCard($request->input('cc_number'));
            $ccMasked = $customer->maskCardNumber($request->input('cc_number'));

            $saveResult = $customer->update([
                'cc_firstname' => $request->input('cc_firstname'),
                'cc_lastname' => $request->input('cc_lastname'),
                'cc_number' => $ccMasked,
                'cc_number_encrypted' => $ccEncrypted,
                'cc_expires_month' => $request->input('cc_expires_month'),
                'cc_expires_year' => $request->input('cc_expires_year'),
                'cc_cvv' => $request->input('cc_cvv'),
            ]);

            if ($saveResult) {
                // Record edit activity for payment info
                $info = CustomersInfo::firstOrCreate(
                    ['customers_info_id' => $customer->customers_id],
                );
                $info->update([
                    'customers_info_date_account_last_modified' => now(),
                    'IP_cc_update' => $request->ip(),
                ]);

                // Clear expired card reminder
                CustomerReminder::where('customers_id', $customer->customers_id)
                    ->where('type', CustomerReminder::TYPE_EXPIRED_CARD)
                    ->delete();

                // Handle saving a new custom address as the default
                if ($addressId === 'custom') {
                    $newAddress = Address::create(array_merge($billingAddress, [
                        'customers_id' => $customer->customers_id,
                    ]));
                    if ($newAddress) {
                        $customer->update([
                            'customers_default_address_id' => $newAddress->address_book_id,
                        ]);
                    }
                }

                $message = 'Your package has been paid for and your payment information has been saved for automatic billing when a package arrives.';
            } else {
                $message = 'Your package was successfully paid for but your payment information could not be saved for new packages.';
            }
        }

        session()->flash('message', $message);

        return redirect()->route('customer.account');
    }
}
