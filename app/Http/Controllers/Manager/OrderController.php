<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\ChargeOrderRequest;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderData;
use App\Models\OrderStatus;
use App\Models\OrderStatusHistory;
use App\Models\OrderLineItems\OrderShipping;
use App\Models\OrderLineItems\OrderTotal;
use App\Services\PaymentService;
use App\Services\Shipping\EndiciaService;
use App\Services\Shipping\FedexService;
use App\Services\Shipping\UspsService;
use App\Services\Shipping\ZebraLabelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    /**
     * Search and list orders.
     */
    public function search(Request $request): View
    {
        $search = $request->query('q');
        $showStatus = $request->query('showStatus');
        $fromThePast = $request->query('from_the_past', config('search.date.default', '-6 months'));
        $results = collect();
        $customRequests = collect();

        if (!empty($search)) {
            $query = Order::with(['status', 'customer', 'total']);

            // Build search conditions: search by order ID, tracking numbers
            $terms = explode(' ', $search);
            foreach ($terms as $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('orders_id', 'LIKE', '%' . $term . '%')
                      ->orWhere('usps_track_num', 'LIKE', '%' . $term . '%')
                      ->orWhere('usps_track_num_in', 'LIKE', '%' . $term . '%')
                      ->orWhere('ups_track_num', 'LIKE', '%' . $term . '%')
                      ->orWhere('fedex_track_num', 'LIKE', '%' . $term . '%')
                      ->orWhere('dhl_track_num', 'LIKE', '%' . $term . '%');
                });
            }

            if (!empty($fromThePast) && $fromThePast !== 'all') {
                $fromDate = date_create($fromThePast);
                if ($fromDate) {
                    $query->where('date_purchased', '>=', $fromDate->format('Y-m-d H:i:s'));
                }
            }

            if (!empty($showStatus)) {
                $query->where('orders_status', $showStatus);
            }

            $results = $query->orderByDesc('date_purchased')->paginate(20)->appends($request->query());
        }

        $statusFilterOptions = OrderStatus::pluck('orders_status_name', 'orders_status_id');
        $userIsManager = auth('admin')->user()->isManager();

        return view('manager.orders.search', compact(
            'search',
            'results',
            'fromThePast',
            'statusFilterOptions',
            'showStatus',
            'userIsManager',
            'customRequests'
        ));
    }

    /**
     * View a single order.
     */
    public function view(int $id): View
    {
        $order = Order::with(['customer', 'status', 'lineItems', 'data'])
            ->findOrFail($id);

        // Look up creator email if set
        $creator = null;
        if (!empty($order->creator_id)) {
            $admin = Admin::find($order->creator_id);
            $creator = $admin ? $admin->email : $order->creator_id;
        }

        $currentStatusHistory = OrderStatusHistory::findCurrentStatus($id)->first();
        $statusHistories = OrderStatusHistory::with('status')
            ->where('orders_id', $id)
            ->orderByDesc('date_added')
            ->get();
        $ordersStatuses = OrderStatus::pluck('orders_status_name', 'orders_status_id');

        // Build order charges from line items
        $orderCharges = $order->lineItems;

        $invoiceCustomer = $this->checkForInvoiceCustomer($order->customer);

        // Determine label printing details
        $action = 'Print';
        if ($order->mail_class !== 'FEDEX') {
            $mailClass = 'usps';
            $url = '#';
            $reprint = false;
            $xml = null;
        } else {
            $mailClass = 'fedex';
            $url = route('manager.orders.print-fedex', ['id' => $order->orders_id]);
            $reprint = OrderData::getValue($id, 'fedex-zpl') !== null;
            if ($reprint) {
                $url = route('manager.orders.print-fedex', ['id' => $order->orders_id, 'reprint' => 'reprint']);
                $action = 'Reprint';
            }
            $xml = null;
        }

        return view('manager.orders.view', compact(
            'order',
            'creator',
            'currentStatusHistory',
            'statusHistories',
            'ordersStatuses',
            'orderCharges',
            'invoiceCustomer',
            'xml',
            'mailClass',
            'url',
            'reprint',
            'action'
        ));
    }

    /**
     * Show the add order form for a customer.
     */
    public function add(int $customerId): View|RedirectResponse
    {
        $customer = Customer::findOrFail($customerId);

        $customersAddresses = Address::where('customers_id', $customerId)
            ->get()
            ->pluck('full', 'address_book_id');

        if ($customersAddresses->isEmpty()) {
            session()->flash('message', 'This customer has insufficient address data for an order to be created.');
            return redirect()->route('manager.dashboard');
        }

        $orderStatuses = OrderStatus::pluck('orders_status_name', 'orders_status_id');

        $requests = \App\Models\CustomPackageRequest::where('customers_id', $customerId)
            ->where('package_status', 1)
            ->get();

        $customerIsReadonly = true;

        return view('manager.orders.add', compact(
            'customer',
            'customersAddresses',
            'orderStatuses',
            'requests',
            'customerIsReadonly'
        ));
    }

    /**
     * Store a new order for a customer.
     */
    public function store(StoreOrderRequest $request, int $customerId): RedirectResponse
    {
        $customer = Customer::findOrFail($customerId);

        $data = $request->validated();
        $data['customers_id'] = $customerId;
        $data['customers_telephone'] = $customer->customers_telephone;
        $data['customers_email_address'] = $customer->customers_email_address;
        $data['creator_id'] = auth('admin')->id();

        // Set insurance coverage from customer defaults if not provided
        if (empty($data['insurance_coverage'])) {
            $data['insurance_coverage'] = $customer->insurance_amount;
        }
        if (isset($data['insurance']) && !$data['insurance']) {
            unset($data['insurance_coverage']);
        }

        // Set mail class from customer default if not provided
        if (empty($data['mail_class']) && !empty($customer->default_postal_type)) {
            $data['mail_class'] = strtoupper($customer->default_postal_type);
        }

        // Marshal address data into order fields
        $addressTypes = [
            'customers' => $customer->customers_default_address_id,
            'delivery' => $request->input('delivery_address_id', $customer->customers_shipping_address_id),
            'billing' => $customer->customers_default_address_id,
        ];

        foreach ($addressTypes as $type => $addressId) {
            if (!empty($addressId)) {
                $address = Address::with(['zone', 'country'])->find($addressId);
                if ($address) {
                    $name = trim($address->entry_firstname . ' ' . $address->entry_lastname);
                    $company = $address->entry_company ?: substr($name, 0, 32);
                    $data[$type . '_name'] = $name;
                    $data[$type . '_company'] = $company;
                    $data[$type . '_street_address'] = $address->entry_street_address;
                    $data[$type . '_suburb'] = $address->entry_suburb;
                    $data[$type . '_city'] = $address->entry_city;
                    $data[$type . '_postcode'] = $address->entry_postcode;
                    $data[$type . '_state'] = $address->zone?->zone_code ?? '';
                    $data[$type . '_country'] = $address->country?->countries_name ?? '';
                    $data[$type . '_address_format_id'] = 2;
                }
            }
        }

        // Invoice customers
        if ($this->checkForInvoiceCustomer($customer)) {
            $data['billing_status'] = 5;
        }

        $data['date_purchased'] = now();

        try {
            $order = Order::create($data);

            // Create default line items for the order
            $this->createDefaultLineItems($order);

            session()->flash('message', 'The order has been created.');

            return redirect()->route('manager.orders.charge', ['id' => $order->orders_id]);
        } catch (\Exception $e) {
            Log::error('OrderController::store: ' . $e->getMessage());
            session()->flash('message', 'The order could not be saved. Please, try again.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show and process the charge page for an order.
     */
    public function charge(Request $request, int $id, PaymentService $paymentService): View|RedirectResponse
    {
        $order = Order::with([
            'customer',
            'status',
            'shipping',
            'fee',
            'insurance',
            'battery',
            'repack',
            'storage',
            'returnItem',
            'misaddressed',
            'shipToUS',
            'subtotal',
            'total',
        ])->findOrFail($id);

        $allowCharge = $this->checkIfOrderCanBeCharged($order);
        $feeRates = config('orders.feeRates', []);
        $invoiceCustomer = $this->checkForInvoiceCustomer($order->customer);

        if ($request->isMethod('post') || $request->isMethod('put')) {
            if ($allowCharge['allow']) {
                $submitAction = $request->input('submit');

                // Save order total updates
                $this->saveOrderTotals($request, $order);

                if ($submitAction === 'charge') {
                    $totalAmount = $order->total ? $order->total->value : 0;

                    if ($invoiceCustomer) {
                        // Record invoice payment
                        $order->update([
                            'orders_status' => 4,
                            'payment_method' => 'invoice',
                            'last_modified' => now(),
                        ]);
                        OrderStatusHistory::record($id, 4, 'Invoice payment recorded');
                        session()->flash('message', 'Order was successfully invoiced.');
                    } else {
                        // Charge the customer's card
                        $cardToken = $order->customer->card_token;
                        if ($cardToken) {
                            $result = $paymentService->chargeCard($cardToken, $totalAmount, 'Order #' . $id);

                            if ($result['success']) {
                                $order->update([
                                    'orders_status' => 4,
                                    'payment_method' => 'cc',
                                    'trans_id' => $result['payment_id'] ?? '',
                                    'last_modified' => now(),
                                ]);
                                OrderStatusHistory::record($id, 4, 'Payment charged to card');
                                session()->flash('message', 'Order was successfully charged to card.');
                            } else {
                                // Mark as awaiting payment on failure
                                $order->update([
                                    'orders_status' => 2,
                                    'last_modified' => now(),
                                ]);
                                OrderStatusHistory::record($id, 2, 'Charge failed: ' . ($result['error'] ?? 'Unknown error'));
                                session()->flash('message', 'Order payment could not be processed. Error: ' . ($result['error'] ?? 'Unknown'));
                            }
                        } else {
                            $order->update([
                                'orders_status' => 2,
                                'last_modified' => now(),
                            ]);
                            OrderStatusHistory::record($id, 2, 'No payment method on file');
                            session()->flash('message', 'No payment method on file. Customer has been notified of awaiting payment status.');
                        }
                    }
                } else {
                    session()->flash('message', 'Order totals have been saved.');
                }

                return redirect()->route('manager.orders.view', ['id' => $id]);
            }
        }

        return view('manager.orders.charge', compact(
            'order',
            'allowCharge',
            'feeRates',
            'invoiceCustomer'
        ));
    }

    /**
     * Mark an order as shipped.
     */
    public function markAsShipped(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $updated = $order->update([
            'orders_status' => 3,
            'orders_date_finished' => now(),
            'last_modified' => now(),
        ]);

        if ($updated) {
            OrderStatusHistory::record($id, 3, 'Marked as shipped', true);
            session()->flash('message', sprintf('Order #%d has been marked as shipped.', $id));
        } else {
            session()->flash('message', sprintf('Order #%d could not be marked as shipped. Please try again.', $id));
        }

        return redirect()->route('manager.orders.view', ['id' => $id]);
    }

    /**
     * Update the status of an order.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        $newStatus = $request->input('orders_status');
        if (empty($newStatus)) {
            session()->flash('message', 'Missing required key: orders_status. Please, try again.');
            return redirect()->back();
        }

        $updateData = [
            'orders_status' => $newStatus,
            'last_modified' => now(),
        ];

        // If marking as shipped, set finished date and track number
        if ($newStatus == 3) {
            $updateData['orders_date_finished'] = now();
            if ($request->has('usps_track_num')) {
                $updateData['usps_track_num'] = $request->input('usps_track_num');
            }
        }

        if ($order->update($updateData)) {
            $comments = $request->input('status_history_comments', '');
            $notified = (bool) $request->input('notify_customer', false);
            OrderStatusHistory::record($id, $newStatus, $comments, $notified);

            session()->flash('message', sprintf('The status for order # %s has been updated.', $id));

            // If the new status is shipped, redirect to dashboard
            if ($newStatus == 3) {
                return redirect()->route('manager.dashboard');
            }

            return redirect()->route('manager.orders.view', ['id' => $id]);
        }

        session()->flash('message', "The order's status could not be saved. Please, try again.");
        return redirect()->back();
    }

    /**
     * Delete an order along with its order totals.
     */
    public function deleteOrder(Request $request, int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $customerId = $request->input('customer_id', $order->customers_id);

        DB::transaction(function () use ($order) {
            // Delete line items
            $order->lineItems()->delete();
            // Delete status history
            $order->statusHistory()->delete();
            // Delete the order
            $order->delete();
        });

        session()->flash('message', 'The order has been deleted.');

        if ($customerId) {
            return redirect()->route('manager.customers.view', ['id' => $customerId]);
        }

        return redirect()->route('manager.orders.search');
    }

    /**
     * Show order status totals.
     */
    public function statusTotals(): View
    {
        $statusCounts = Order::select('orders_status', DB::raw('COUNT(*) as count'))
            ->groupBy('orders_status')
            ->get()
            ->pluck('count', 'orders_status');

        $statuses = OrderStatus::all()->keyBy('orders_status_id');

        return view('manager.orders.status-totals', compact('statusCounts', 'statuses'));
    }

    /**
     * Print shipping label for an order (AJAX / ZPL).
     */
    public function printLabel(Request $request, int $id, ZebraLabelService $zebraLabel): Response|RedirectResponse
    {
        $order = Order::with('customer')->findOrFail($id);

        $labelData = $this->prepareLabelData($order);
        $result = $zebraLabel->printLabel($labelData);

        if ($request->ajax()) {
            if (isset($result['error'])) {
                return response($result['error'], 500)->header('Content-Type', 'text/plain');
            }
            $zpl = $result['data'] ?? '';
            return response($zpl, 200)->header('Content-Type', 'text/plain');
        }

        return redirect()->route('manager.orders.view', ['id' => $id]);
    }

    /**
     * Print FedEx label for an order (AJAX / ZPL).
     */
    public function printFedex(Request $request, int $id, FedexService $fedex, ?string $reprint = null): Response|RedirectResponse
    {
        $order = Order::with('customer')->findOrFail($id);

        if ($reprint || $request->query('reprint')) {
            // Try to get existing label data
            $label = OrderData::getValue($id, 'fedex-zpl');
            if ($label) {
                if ($request->ajax()) {
                    return response($label, 200)->header('Content-Type', 'text/plain');
                }
                return redirect()->route('manager.orders.view', ['id' => $id]);
            }
        }

        // Request new label from FedEx API
        $label = $fedex->printLabel($order);
        if ($label) {
            OrderData::setValue($id, 'fedex-zpl', $label);
            if ($request->ajax()) {
                return response($label, 200)->header('Content-Type', 'text/plain');
            }
        }

        return redirect()->route('manager.orders.view', ['id' => $id]);
    }

    /**
     * Delete a shipping label for an order (remove stored FedEx ZPL data).
     */
    public function deleteLabel(int $id): RedirectResponse
    {
        $deleted = OrderData::where('orders_id', $id)
            ->where('data_type', 'fedex-zpl')
            ->delete();

        if ($deleted) {
            session()->flash('message', 'The FedEx label has been removed.');
        } else {
            session()->flash('message', 'The FedEx label could not be removed.');
        }

        return redirect()->route('manager.orders.view', ['id' => $id]);
    }

    /**
     * Show the orders report.
     */
    public function report(Request $request): View
    {
        $statusFilterOptions = OrderStatus::pluck('orders_status_name', 'orders_status_id');
        $validIntervals = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];
        $validSortFields = ['date_purchased' => 'Date Purchased', 'orders_id' => 'Order ID'];

        $results = collect();
        $interval = 'day';

        $data = $request->all();
        if (!empty($data)) {
            $interval = $data['interval'] ?? 'day';
            $fromDate = $data['from_date'] ?? null;
            $toDate = $data['to_date'] ?? null;
            $orderStatus = $data['orders_status'] ?? null;

            $groupFormat = match ($interval) {
                'week' => '%x-W%v',
                'month' => '%Y-%m',
                'year' => '%Y',
                default => '%Y-%m-%d',
            };

            $query = Order::select(
                DB::raw("DATE_FORMAT(date_purchased, '{$groupFormat}') as period"),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(CASE WHEN orders_status = 4 THEN 1 ELSE 0 END) as paid_orders')
            );

            if ($fromDate) {
                $query->where('date_purchased', '>=', $fromDate);
            }
            if ($toDate) {
                $query->where('date_purchased', '<=', $toDate . ' 23:59:59');
            }
            if ($orderStatus) {
                $query->where('orders_status', $orderStatus);
            }

            $sortField = $data['sort'] ?? 'date_purchased';
            $sortDir = $data['direction'] ?? 'asc';

            $results = $query->groupBy('period')
                ->orderBy('period', $sortDir)
                ->get();
        }

        return view('manager.orders.report', compact(
            'statusFilterOptions',
            'validIntervals',
            'validSortFields',
            'results',
            'interval'
        ));
    }

    // ---------------------------------------------------------------
    // Private Helpers
    // ---------------------------------------------------------------

    /**
     * Check if a customer is an invoice customer.
     */
    protected function checkForInvoiceCustomer(?Customer $customer): bool
    {
        if (!$customer) {
            return false;
        }
        return $customer->invoicing_authorized && $customer->billing_type === 'invoice';
    }

    /**
     * Check if an order can be charged.
     */
    protected function checkIfOrderCanBeCharged(Order $order): array
    {
        if (in_array($order->orders_status, [3, 4])) {
            return [
                'allow' => false,
                'message' => 'This order has already been ' . ($order->orders_status == 3 ? 'shipped' : 'paid') . '.',
            ];
        }

        return ['allow' => true, 'message' => ''];
    }

    /**
     * Create default order line items for a new order.
     */
    protected function createDefaultLineItems(Order $order): void
    {
        $lineItemClasses = [
            \App\Models\OrderLineItems\OrderShipping::class => ['title' => 'Shipping', 'class' => 'ot_shipping'],
            \App\Models\OrderLineItems\OrderFee::class => ['title' => 'Fee', 'class' => 'ot_fee'],
            \App\Models\OrderLineItems\OrderInsurance::class => ['title' => 'Insurance', 'class' => 'ot_insurance'],
            \App\Models\OrderLineItems\OrderStorage::class => ['title' => 'Storage', 'class' => 'ot_storage'],
            \App\Models\OrderLineItems\OrderRepack::class => ['title' => 'Repack', 'class' => 'ot_repack'],
            \App\Models\OrderLineItems\OrderBattery::class => ['title' => 'Battery', 'class' => 'ot_battery'],
            \App\Models\OrderLineItems\OrderReturn::class => ['title' => 'Return', 'class' => 'ot_return'],
            \App\Models\OrderLineItems\OrderMisaddressed::class => ['title' => 'Misaddressed', 'class' => 'ot_misaddressed'],
            \App\Models\OrderLineItems\OrderShipToUS::class => ['title' => 'Ship to US', 'class' => 'ot_ship_to_us'],
            \App\Models\OrderLineItems\OrderSubtotal::class => ['title' => 'Subtotal', 'class' => 'ot_subtotal'],
            \App\Models\OrderLineItems\OrderTotal::class => ['title' => 'Total', 'class' => 'ot_total'],
        ];

        foreach ($lineItemClasses as $class => $attrs) {
            $class::create([
                'orders_id' => $order->orders_id,
                'title' => $attrs['title'],
                'text' => '$0.00',
                'value' => 0.00,
                'class' => $attrs['class'],
                'sort_order' => 0,
            ]);
        }
    }

    /**
     * Save order total updates from the charge form.
     */
    protected function saveOrderTotals(Request $request, Order $order): void
    {
        $lineItemFields = [
            'OrderShipping' => 'shipping',
            'OrderStorage' => 'storage',
            'OrderInsurance' => 'insurance',
            'OrderFee' => 'fee',
            'OrderRepack' => 'repack',
            'OrderBattery' => 'battery',
            'OrderReturn' => 'returnItem',
            'OrderMisaddressed' => 'misaddressed',
            'OrderShipToUS' => 'shipToUS',
        ];

        $subtotal = 0;
        foreach ($lineItemFields as $inputKey => $relation) {
            $value = $request->input("{$inputKey}.value", 0);
            $value = (float) $value;
            $subtotal += $value;

            $lineItem = $order->{$relation};
            if ($lineItem) {
                $lineItem->update([
                    'value' => $value,
                    'text' => '$' . number_format($value, 2),
                ]);
            }
        }

        // Update subtotal
        if ($order->subtotal) {
            $order->subtotal->update([
                'value' => $subtotal,
                'text' => '$' . number_format($subtotal, 2),
            ]);
        }

        // Update total
        if ($order->total) {
            $order->total->update([
                'value' => $subtotal,
                'text' => '$' . number_format($subtotal, 2),
            ]);
        }

        // Update order status if it was in warehouse
        if ($order->orders_status == 1) {
            $order->update([
                'orders_status' => 2,
                'last_modified' => now(),
            ]);
        }
    }

    /**
     * Prepare label data for ZPL printing.
     */
    protected function prepareLabelData(Order $order): array
    {
        $address = PHP_EOL;
        if (!empty($order->delivery_company)) {
            $address .= $order->delivery_company . PHP_EOL;
        }
        $address .= $order->delivery_street_address . PHP_EOL;
        if (!empty($order->delivery_suburb)) {
            $address .= $order->delivery_suburb . PHP_EOL;
        }
        $address .= $order->delivery_city . ', ';
        $address .= $order->delivery_state . ' ';
        $address .= $order->delivery_postcode . PHP_EOL;
        $address .= $order->delivery_country . PHP_EOL . PHP_EOL;
        $address .= $order->customers_email_address;
        if (!empty($order->customers_telephone)) {
            $address .= PHP_EOL . 'Phone: ' . $order->customers_telephone;
        }
        $address .= PHP_EOL;

        return [
            'header' => [
                'size' => 34,
                'content' => $order->delivery_name . ' - ' . ($order->customer->billing_id ?? ''),
            ],
            'body' => [
                'size' => 30,
                'content' => $address,
            ],
            'footer' => [
                'size' => 18,
                'content' => 'Charge failed on ' . date('m/d/Y') . ' for order #' . $order->orders_id,
            ],
        ];
    }
}
