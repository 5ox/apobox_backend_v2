<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\ChargeOrderRequest;
use App\Mail\OrderStatusUpdate;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\OrderLineItems\OrderFee;
use App\Models\OrderLineItems\OrderShipping;
use App\Models\OrderLineItems\OrderInsurance;
use App\Models\OrderLineItems\OrderSubtotal;
use App\Models\OrderLineItems\OrderTotal;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Order statuses that allow a charge to proceed.
     * 1 = Warehouse, 2 = Awaiting Payment
     */
    protected const CHARGEABLE_STATUSES = [1, 2];

    /**
     * Show an order by ID.
     *
     * Ported from CakePHP OrdersController::api_view().
     * Returns the order with its status relationship.
     */
    public function show(int $id): JsonResponse
    {
        $order = Order::with(['status', 'customer', 'total'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Order #' . $id . ' not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'type' => 'orders',
                'id' => $order->orders_id,
                'attributes' => $order->toArray(),
            ],
        ]);
    }

    /**
     * Create a new order for a customer via API.
     *
     * Ported from CakePHP OrdersController::api_add().
     * Accepts a customer ID (either customers_id or billing_id) in
     * the URL, and order attributes in the request body. Marshals
     * address data from the customer's default addresses, creates
     * the order, and sets up initial line items (fee, subtotal, total).
     */
    public function store(StoreOrderRequest $request, int $id): JsonResponse
    {
        // Look up customer by billing_id first, then by customers_id
        $customer = Customer::where('billing_id', $id)->first()
            ?? Customer::find($id);

        if (!$customer) {
            return response()->json([
                'error' => 'Customer ' . $id . ' not found.',
            ], 404);
        }

        try {
            $orderData = $request->validated();

            // Set customer fields on the order
            $orderData['customers_id'] = $customer->customers_id;
            $orderData['customers_telephone'] = $customer->customers_telephone;
            $orderData['customers_email_address'] = $customer->customers_email_address;

            // Insurance coverage defaults to customer's insurance amount
            if (empty($orderData['insurance_coverage'])) {
                $orderData['insurance_coverage'] = $customer->insurance_amount;
            }

            // Set default values
            $orderData['date_purchased'] = now();
            $orderData['last_modified'] = now();
            $orderData['orders_status'] = 1; // Warehouse
            $orderData['currency'] = 'USD';
            $orderData['currency_value'] = 1.0;

            // Check if this is an invoice customer
            if ($this->isInvoiceCustomer($customer)) {
                $orderData['billing_status'] = 5;
            }

            if (empty($orderData['comments'])) {
                $orderData['comments'] = '';
            }

            // Set creator_id from the authenticated API admin user
            $orderData['creator_id'] = $request->user()?->id;

            // Marshal address data from customer's default addresses
            $orderData = $this->marshalAddressesForOrder($orderData, $customer);

            DB::beginTransaction();

            $order = Order::create($orderData);

            // Create default line items
            $weightOz = (int) ($orderData['weight_oz'] ?? 0);

            OrderFee::create([
                'orders_id' => $order->orders_id,
                'value' => OrderFee::getFee($weightOz),
            ]);

            // Create subtotal and total (they auto-calculate on creation)
            $subtotal = OrderSubtotal::create([
                'orders_id' => $order->orders_id,
                'value' => OrderFee::getFee($weightOz),
            ]);

            OrderTotal::create([
                'orders_id' => $order->orders_id,
                'value' => $subtotal->value,
            ]);

            // Record initial status history
            OrderStatusHistory::record(
                $order->orders_id,
                1, // Warehouse
                'Order created via API'
            );

            DB::commit();

            // Refresh with relationships
            $order->load(['status', 'total']);

            Log::info('API: Order created', [
                'orders_id' => $order->orders_id,
                'customers_id' => $customer->customers_id,
            ]);

            return response()->json([
                'data' => [
                    'type' => 'orders',
                    'id' => $order->orders_id,
                    'attributes' => $order->toArray(),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('API: Order creation failed', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'The order could not be saved. ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Charge an order via API.
     *
     * Ported from CakePHP OrdersController::api_charge().
     * Validates the order can be charged, saves updated line item
     * amounts, and processes the payment through PaymentService.
     * For invoice customers, records the invoice without charging.
     */
    public function charge(ChargeOrderRequest $request, int $id): JsonResponse
    {
        $order = Order::with([
            'customer',
            'status',
            'shipping',
            'fee',
            'insurance',
            'subtotal',
            'total',
        ])->find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Invalid order number.',
            ], 404);
        }

        // Check if order can be charged
        $chargeCheck = $this->checkIfOrderCanBeCharged($order);
        if (!$chargeCheck['allow']) {
            return response()->json([
                'error' => $chargeCheck['message'],
            ], 400);
        }

        $customer = $order->customer;
        $submitType = $request->input('submit', 'charge');

        try {
            DB::beginTransaction();

            // Ensure the order has a default fee row
            if (!$order->fee) {
                $weightOz = (int) ($order->weight_oz ?? 0);
                OrderFee::create([
                    'orders_id' => $order->orders_id,
                    'value' => OrderFee::getFee($weightOz),
                ]);
            }

            // Update the order status from Warehouse (1) to Awaiting Payment (2)
            if ($order->orders_status == 1) {
                $order->orders_status = 2;
            }
            $order->last_modified = now();

            // Update line item values if provided in the request
            $this->updateLineItemsFromRequest($request, $order);

            $order->save();

            // Recalculate subtotal and total
            $subtotal = OrderSubtotal::where('orders_id', $order->orders_id)->first();
            if ($subtotal) {
                $subtotal->calculateTotal();
            }
            $total = OrderTotal::where('orders_id', $order->orders_id)->first();
            if ($total) {
                $total->updateTotal();
            }

            // Reload order with fresh totals
            $order->refresh();
            $order->load(['total', 'customer']);

            $totalValue = $order->total ? (float) $order->total->value : 0.00;

            if ($submitType === 'charge') {
                $isInvoiceCustomer = $this->isInvoiceCustomer($customer);

                if ($isInvoiceCustomer) {
                    // Record invoice payment (no actual charge)
                    $order->update([
                        'payment_method' => 'Invoice',
                        'cc_type' => 'Invoice',
                        'cc_owner' => $customer->customers_firstname . ' ' . $customer->customers_lastname,
                        'cc_number' => 'Invoice',
                        'cc_expires' => 'INV',
                        'orders_status' => 1, // Back to Warehouse for invoice customers
                        'billing_status' => 5,
                    ]);

                    OrderStatusHistory::record(
                        $order->orders_id,
                        $order->orders_status,
                        'Invoiced via API'
                    );
                } else {
                    // Charge the customer's card
                    $cardToken = $customer->card_token;

                    if (empty($cardToken)) {
                        DB::rollBack();
                        return response()->json([
                            'error' => 'Customer does not have a valid payment method on file.',
                        ], 422);
                    }

                    $paymentService = app(PaymentService::class);
                    $result = $paymentService->chargeCard(
                        $cardToken,
                        $totalValue,
                        'Order #' . $order->orders_id
                    );

                    if (!$result['success']) {
                        // Charge failed
                        Log::warning('API: Order charge failed', [
                            'orders_id' => $order->orders_id,
                            'error' => $result['error'] ?? 'Unknown',
                        ]);

                        $this->sendOrderStatusEmail($order);

                        DB::commit();

                        return response()->json([
                            'error' => $result['error'] ?? 'The charge could not be processed.',
                        ], 400);
                    }

                    // Charge successful - record payment
                    $order->update([
                        'payment_method' => 'Payments Pro',
                        'cc_type' => '',
                        'cc_owner' => $customer->cc_firstname . ' ' . $customer->cc_lastname,
                        'cc_number' => $customer->cc_number,
                        'cc_expires' => sprintf('%02d', $customer->cc_expires_month)
                            . sprintf('%02d', $customer->cc_expires_year),
                        'orders_status' => 4, // Paid
                        'billing_status' => 4,
                        'last_modified' => now(),
                        'trans_id' => $result['payment_id'] ?? null,
                    ]);

                    OrderStatusHistory::record(
                        $order->orders_id,
                        4, // Paid
                        'Charged via API',
                        true // customer notified
                    );
                }

                $this->sendOrderStatusEmail($order);
            }
            // If submit type is not 'charge', we just save the amounts
            // without processing payment (used for updating line items before charge)

            DB::commit();

            // Reload order with all line items for response
            $order->refresh();
            $order->load([
                'shipping', 'storage', 'insurance', 'fee',
                'repack', 'inspection', 'returnItem', 'misaddressed',
                'shipToUS', 'subtotal', 'total', 'status',
            ]);

            Log::info('API: Order charge processed', [
                'orders_id' => $order->orders_id,
                'submit_type' => $submitType,
            ]);

            return response()->json([
                'data' => [
                    'type' => 'orders',
                    'id' => $order->orders_id,
                    'attributes' => [
                        'orders_status' => $order->orders_status,
                        'payment_method' => $submitType === 'charge' ? $order->payment_method : null,
                    ],
                    'relationships' => $this->buildLineItemRelationships($order),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('API: Order charge exception', [
                'orders_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred while processing the charge.',
            ], 500);
        }
    }

    /**
     * Change order status via API.
     *
     * Ported from CakePHP OrdersController::api_changestatus() and
     * _changeOrderStatus(). Accepts an order ID, new status, optional
     * tracking number (required for shipped status 3), optional
     * comments, and optional notify_customer flag.
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $request->validate([
            'data.id' => ['required', 'integer'],
            'data.attributes.orders_status' => ['required', 'integer', 'in:1,2,3,4,5'],
            'data.attributes.usps_track_num' => ['nullable', 'string', 'max:64'],
            'data.attributes.status_history_comments' => ['nullable', 'string', 'max:1000'],
            'data.attributes.notify_customer' => ['nullable', 'boolean'],
        ]);

        $id = $request->input('data.id');
        $attributes = $request->input('data.attributes');
        $newStatus = (int) $attributes['orders_status'];

        $order = Order::with(['status', 'customer'])->find($id);

        if (!$order) {
            return response()->json([
                'error' => 'Order not found.',
            ], 404);
        }

        // Tracking number is required when marking as shipped (status 3)
        if ($newStatus === 3 && empty($attributes['usps_track_num'])) {
            return response()->json([
                'error' => 'Tracking number required for shipped orders.',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Build update data
            $updateData = [
                'orders_status' => $newStatus,
                'last_modified' => now(),
            ];

            // Set tracking number if provided
            if (!empty($attributes['usps_track_num'])) {
                $updateData['usps_track_num'] = $attributes['usps_track_num'];
            }

            // Calculate turnaround time for shipped orders
            if ($newStatus === 3 && $order->date_purchased) {
                $updateData['turnaround_sec'] = now()->diffInSeconds($order->date_purchased);
            }

            $order->update($updateData);

            // Record status history
            $comments = $attributes['status_history_comments'] ?? '';
            $notifyCustomer = !empty($attributes['notify_customer']);

            OrderStatusHistory::record(
                $order->orders_id,
                $newStatus,
                $comments,
                $notifyCustomer
            );

            // Send notification email if requested
            if ($notifyCustomer && $order->customer) {
                $this->sendOrderStatusEmail($order, $comments);
            }

            DB::commit();

            Log::info('API: Order status changed', [
                'orders_id' => $id,
                'new_status' => $newStatus,
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('API: Order status change failed', [
                'orders_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to update order status.',
            ], 400);
        }
    }

    /**
     * Check whether an order is in a valid state to be charged.
     *
     * Ported from CakePHP Order::checkIfOrderCanBeCharged().
     */
    protected function checkIfOrderCanBeCharged(Order $order): array
    {
        if (!in_array($order->orders_status, self::CHARGEABLE_STATUSES)) {
            $statusName = $order->status
                ? ucfirst($order->status->orders_status_name)
                : 'Unknown';

            return [
                'allow' => false,
                'message' => 'Orders cannot be charged while in status: ' . $statusName,
            ];
        }

        if ($order->customer && !$order->customer->is_active) {
            Log::warning('API: Charge attempted on closed account', [
                'orders_id' => $order->orders_id,
                'billing_id' => $order->customer->billing_id,
            ]);

            return [
                'allow' => false,
                'message' => 'The order can not be charged because customer '
                    . $order->customer->billing_id . ' has a closed account.',
            ];
        }

        return ['allow' => true];
    }

    /**
     * Check if a customer is authorized for invoice billing.
     *
     * Ported from CakePHP Order::checkForInvoiceCustomer().
     */
    protected function isInvoiceCustomer(Customer $customer): bool
    {
        return $customer->billing_type === 'invoice'
            && $customer->invoicing_authorized === true;
    }

    /**
     * Marshal customer address data into the order fields.
     *
     * Ported from CakePHP OrdersController::_setAddressesForOrder()
     * and _fallbackToDefaultAddresses() and _marshalAddressTo().
     * Copies the customer's default/shipping/billing addresses
     * into the corresponding order columns.
     */
    protected function marshalAddressesForOrder(array $orderData, Customer $customer): array
    {
        $addressMapping = [
            'customers' => $customer->customers_default_address_id,
            'delivery' => $customer->customers_shipping_address_id,
            'billing' => $customer->customers_default_address_id,
        ];

        foreach ($addressMapping as $type => $addressId) {
            if (empty($addressId)) {
                continue;
            }

            $address = Address::with(['zone', 'country'])->find($addressId);

            if (!$address || $address->customers_id != $customer->customers_id) {
                continue;
            }

            $name = trim($address->entry_firstname . ' ' . $address->entry_lastname);
            $company = !empty($address->entry_company)
                ? $address->entry_company
                : substr($name, 0, 32);

            $orderData[$type . '_name'] = $name;
            $orderData[$type . '_company'] = $company;
            $orderData[$type . '_street_address'] = $address->entry_street_address ?? '';
            $orderData[$type . '_suburb'] = $address->entry_suburb ?? '';
            $orderData[$type . '_city'] = $address->entry_city ?? '';
            $orderData[$type . '_postcode'] = $address->entry_postcode ?? '';
            $orderData[$type . '_state'] = $address->zone?->zone_code ?? '';
            $orderData[$type . '_country'] = $address->country?->countries_name ?? '';
            $orderData[$type . '_address_format_id'] = 2;
        }

        return $orderData;
    }

    /**
     * Update order line item values from the charge request.
     *
     * Maps request fields to the appropriate OrderLineItem subclasses.
     */
    protected function updateLineItemsFromRequest(ChargeOrderRequest $request, Order $order): void
    {
        $lineItemMap = [
            'OrderShipping' => ['class' => OrderShipping::class, 'field' => 'shipping_value'],
            'OrderInsurance' => ['class' => OrderInsurance::class, 'field' => 'insurance_value'],
        ];

        foreach ($lineItemMap as $key => $config) {
            $value = $request->input($config['field']);
            if ($value !== null) {
                $config['class']::updateOrCreate(
                    ['orders_id' => $order->orders_id],
                    ['value' => $value]
                );
            }
        }

        // Update mail_class if provided
        if ($request->has('mail_class')) {
            $order->mail_class = $request->input('mail_class');
        }
    }

    /**
     * Build line item relationships array for charge response.
     *
     * Ported from CakePHP OrdersController::_sendChargeResponse().
     */
    protected function buildLineItemRelationships(Order $order): array
    {
        $models = [
            'shipping' => $order->shipping,
            'storage' => $order->storage,
            'insurance' => $order->insurance,
            'fee' => $order->fee,
            'repack' => $order->repack,
            'inspection' => $order->inspection,
            'return' => $order->returnItem,
            'misaddressed' => $order->misaddressed,
            'ship_to_us' => $order->shipToUS,
            'subtotal' => $order->subtotal,
            'total' => $order->total,
        ];

        $relationships = [];
        foreach ($models as $key => $lineItem) {
            $relationships[$key] = [
                'data' => [
                    'value' => $lineItem ? (float) $lineItem->value : 0.00,
                ],
            ];
        }

        return $relationships;
    }

    /**
     * Send an order status update email to the customer.
     *
     * Ported from CakePHP Order::sendStatusUpdateEmail().
     */
    protected function sendOrderStatusEmail(Order $order, ?string $comments = null): void
    {
        $order->load(['customer', 'status']);

        if (!$order->customer) {
            return;
        }

        $statusName = $order->status?->orders_status_name ?? 'Unknown';

        try {
            Mail::to($order->customer->customers_email_address)
                ->queue(new OrderStatusUpdate(
                    $order->customer->customers_firstname,
                    $order->customer->customers_lastname,
                    (string) $order->orders_id,
                    $statusName,
                    $comments
                ));
        } catch (\Exception $e) {
            Log::error('Failed to send order status email', [
                'orders_id' => $order->orders_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
