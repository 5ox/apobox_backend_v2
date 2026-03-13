<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentInfoRequest;
use App\Http\Requests\UpdateContactInfoRequest;
use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomersInfo;
use App\Models\Order;
use App\Models\SearchIndex;
use App\Models\ShippingAddress;
use App\Services\CreditCardService;
use App\Services\ZendeskService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Search and list customers by billing ID, email, phone, or name.
     * Uses FULLTEXT search_indices table for fast fuzzy matching.
     */
    public function search(Request $request): View|RedirectResponse|JsonResponse
    {
        $search = trim((string) $request->query('q', ''));
        $results = collect();
        $isAjax = $request->wantsJson() || $request->ajax();

        if ($search !== '') {
            $normalizedSearch = mb_strtolower($search);

            // Phase 1: exact match redirect (uses indexed columns — instant)
            $directMatch = Customer::query()
                ->where(function (Builder $query) use ($normalizedSearch, $search) {
                    $query->where('billing_id', $normalizedSearch)
                        ->orWhere('customers_email_address', $normalizedSearch)
                        ->orWhere('backup_email_address', $normalizedSearch);

                    if (ctype_digit($search)) {
                        $query->orWhere('customers_id', (int) $search);
                    }
                })
                ->first();

            if ($directMatch) {
                if ($isAjax) {
                    return response()->json([
                        'redirect' => route(auth('admin')->user()->role . '.customers.view', ['id' => $directMatch->customers_id]),
                    ]);
                }
                return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $directMatch->customers_id]);
            }

            // Phase 2: FULLTEXT search via search_indices (covers name, billing_id,
            // email, phone, backup_email, and authorized names — all pre-indexed)
            $booleanQuery = SearchIndex::toBooleanQuery($search);

            if ($booleanQuery !== '') {
                $indexHits = SearchIndex::where('model', Customer::class)
                    ->whereRaw('MATCH(data) AGAINST(? IN BOOLEAN MODE)', [$booleanQuery])
                    ->orderByRaw('MATCH(data) AGAINST(? IN BOOLEAN MODE) DESC', [$booleanQuery])
                    ->pluck('foreign_key');

                if ($indexHits->isNotEmpty()) {
                    $ids = $indexHits->toArray();
                    $idList = implode(',', $ids);

                    $results = Customer::whereIn('customers_id', $ids)
                        ->orderByRaw("FIELD(customers_id, {$idList})")
                        ->paginate(20)
                        ->appends($request->query());
                }
            }

            // Phase 3: If FULLTEXT returned nothing (short terms, special chars),
            // fall back to a targeted LIKE on indexed columns only
            if ($results instanceof \Illuminate\Support\Collection || $results->total() === 0) {
                $searchLike = '%' . $this->escapeLike($normalizedSearch) . '%';

                $query = Customer::query()
                    ->where(function (Builder $q) use ($searchLike, $search) {
                        $q->where('billing_id', 'LIKE', $searchLike)
                          ->orWhere('customers_email_address', 'LIKE', $searchLike)
                          ->orWhere('customers_lastname', 'LIKE', $searchLike)
                          ->orWhere('customers_firstname', 'LIKE', $searchLike);

                        if (ctype_digit($search)) {
                            $q->orWhere('customers_id', (int) $search);
                        }
                    })
                    ->orderBy('customers_lastname')
                    ->orderBy('customers_firstname');

                $results = $query->paginate(20)->appends($request->query());
            }

            // Single-result shortcut
            if ($results->total() === 1) {
                $first = $results->first();
                if ($isAjax) {
                    return response()->json([
                        'redirect' => route(auth('admin')->user()->role . '.customers.view', ['id' => $first->customers_id]),
                    ]);
                }
                return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $first->customers_id]);
            }
        }

        // AJAX: return JSON for live search
        if ($isAjax) {
            $items = $results instanceof \Illuminate\Pagination\LengthAwarePaginator
                ? $results->map(fn(Customer $c) => [
                    'customers_id' => $c->customers_id,
                    'billing_id'   => $c->billing_id,
                    'full_name'    => $c->full_name,
                    'email'        => $c->customers_email_address,
                ])
                : [];

            return response()->json([
                'results' => $items,
                'total'   => $results instanceof \Illuminate\Pagination\LengthAwarePaginator ? $results->total() : 0,
            ]);
        }

        $userIsManager = auth('admin')->user()->isManager();

        return view('manager.customers.search', compact('search', 'results', 'userIsManager'));
    }

    /**
     * View a customer's details by internal ID.
     */
    public function view(int $id): View
    {
        $customer = Customer::with([
            'addresses',
            'defaultAddress.zone',
            'shippingAddress.zone',
            'emergencyAddress.zone',
            'authorizedNames',
        ])->findOrFail($id);

        $customer->name = $customer->full_name;

        $orders = Order::with(['status', 'customer', 'total'])
            ->where('customers_id', $id)
            ->orderByDesc('date_purchased')
            ->paginate(20);

        $closed = null;
        if (!$customer->is_active) {
            $info = CustomersInfo::find($id);
            if ($info && $info->customers_info_date_account_created) {
                $closedField = $info->getAttribute('customers_info_date_account_closed');
                $closed = $closedField ? date('m/d/Y', strtotime($closedField)) : null;
            }
        }

        $userIsManager = auth('admin')->user()->isManager();
        $partialSignup = empty($customer->billing_id);
        $customRequests = $customer->orders()
            ->with('customPackageRequests')
            ->get()
            ->pluck('customPackageRequests')
            ->flatten();

        // Fetch Zendesk tickets for this customer
        $zendesk = app(ZendeskService::class);
        $zendeskTickets = [];
        $zendeskError = null;
        $zendeskConfigured = $zendesk->isConfigured();

        if ($zendeskConfigured && $customer->customers_email_address) {
            try {
                $zendeskTickets = $zendesk->getTicketsForEmail($customer->customers_email_address);
            } catch (\Exception $e) {
                $zendeskError = $e->getMessage();
            }
        }

        return view('manager.customers.view', compact(
            'customer',
            'orders',
            'userIsManager',
            'partialSignup',
            'customRequests',
            'closed',
            'zendeskTickets',
            'zendeskError',
            'zendeskConfigured'
        ));
    }

    /**
     * View a customer's details by billing ID (e.g., AB1234).
     * Redirects to the standard view route.
     */
    public function viewByBillingId(string $billingId): RedirectResponse
    {
        $customer = Customer::whereRaw('LOWER(billing_id) = ?', [mb_strtolower($billingId)])->firstOrFail();

        return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $customer->customers_id]);
    }

    /**
     * Show recent orders for a customer.
     */
    public function recentOrders(int $id): View
    {
        $customer = Customer::findOrFail($id);
        $customerName = $customer->full_name;

        $orders = Order::with(['status', 'customer'])
            ->select([
                'orders_id',
                'customers_id',
                'comments',
                'last_modified',
                'date_purchased',
                'orders_date_finished',
                'usps_track_num',
                'usps_track_num_in',
                'ups_track_num',
                'dhl_track_num',
                'fedex_track_num',
                'width',
                'length',
                'depth',
                'weight_oz',
                'mail_class',
                'orders_status',
                'delivery_name',
                'delivery_street_address',
                'delivery_city',
                'delivery_state',
                'delivery_postcode',
            ])
            ->where('customers_id', $id)
            ->orderByDesc('date_purchased')
            ->paginate(20);

        return view('manager.customers.recent-orders', compact('orders', 'customerName'));
    }

    /**
     * Show the payment info edit form for a customer.
     */
    public function editPaymentInfo(int $id): View
    {
        $customer = Customer::findOrFail($id);

        // Do not show full card data -- mask sensitive fields
        $customer->cc_number = '';
        $customer->cc_expires_month = '';
        $customer->cc_expires_year = '';
        $customer->cc_cvv = '';

        return view('manager.customers.edit-payment-info', compact('customer'));
    }

    /**
     * Update a customer's payment info.
     */
    public function updatePaymentInfo(UpdatePaymentInfoRequest $request, int $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $fields = [
            'cc_firstname',
            'cc_lastname',
            'cc_number',
            'cc_expires_month',
            'cc_expires_year',
            'cc_cvv',
        ];

        $data = $request->only($fields);

        // Encrypt the credit card number
        if (!empty($data['cc_number'])) {
            $ccService = app(CreditCardService::class);
            $data['cc_number_encrypted'] = $ccService->encrypt($data['cc_number']);
            $data['cc_number'] = $ccService->maskNumber($data['cc_number']);

            // Generate a payment token via PayPal vault
            $cardToken = app(\App\Services\PaymentService::class)->storeCard([
                'number' => $request->input('cc_number'),
                'expire_month' => $data['cc_expires_month'],
                'expire_year' => $data['cc_expires_year'],
                'cvv' => $data['cc_cvv'],
                'first_name' => $data['cc_firstname'],
                'last_name' => $data['cc_lastname'],
            ]);
            if ($cardToken) {
                $data['card_token'] = $cardToken;
            }
        }

        $customer->update($data);

        if (!empty($data['cc_number']) && empty($data['card_token'] ?? null)) {
            session()->flash('warning', 'Card saved locally but PayPal vault token could not be created. Charges may fail until the token is generated.');
        } else {
            session()->flash('message', "The customer's credit card has been updated.");
        }

        return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $id]);
    }

    /**
     * Show the contact info edit form for a customer.
     */
    public function editContactInfo(int $id): View
    {
        $customer = Customer::findOrFail($id);

        return view('manager.customers.edit-contact-info', compact('customer'));
    }

    /**
     * Update a customer's contact info.
     */
    public function updateContactInfo(UpdateContactInfoRequest $request, int $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $fields = [
            'customers_firstname',
            'customers_lastname',
            'customers_email_address',
            'customers_telephone',
            'customers_fax',
            'backup_email_address',
            'invoicing_authorized',
            'billing_type',
        ];

        $customer->update($request->only($fields));

        session()->flash('message', "The customer's information has been updated.");

        return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $id]);
    }

    /**
     * Show all addresses for a customer (JSON response for AJAX).
     */
    public function addresses(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $addresses = Address::where('customers_id', $id)
            ->get()
            ->pluck('full', 'address_book_id');

        return response()->json(['addresses' => $addresses]);
    }

    /**
     * Show shipping addresses for a customer (JSON for AJAX).
     */
    public function shippingAddresses(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $shippingAddresses = ShippingAddress::where('customers_id', $id)
            ->get()
            ->pluck('full', 'address_book_id');

        return response()->json(['shippingAddresses' => $shippingAddresses]);
    }

    /**
     * Show the default addresses edit form for a customer.
     */
    public function editDefaultAddresses(int $id): View
    {
        $customer = Customer::findOrFail($id);

        $addresses = Address::where('customers_id', $id)
            ->get()
            ->pluck('full', 'address_book_id');

        $customersDefaultAddresses = $addresses;
        $customersShippingAddresses = $addresses;
        $customersEmergencyAddresses = $addresses;

        return view('manager.customers.edit-default-addresses', compact(
            'customer',
            'customersDefaultAddresses',
            'customersShippingAddresses',
            'customersEmergencyAddresses'
        ));
    }

    /**
     * Update a customer's default addresses.
     */
    public function updateDefaultAddresses(Request $request, int $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customers_default_address_id' => ['nullable', 'integer', 'exists:address_book,address_book_id'],
            'customers_shipping_address_id' => ['nullable', 'integer', 'exists:address_book,address_book_id'],
            'customers_emergency_address_id' => ['nullable', 'integer', 'exists:address_book,address_book_id'],
        ]);

        $fields = [
            'customers_default_address_id',
            'customers_shipping_address_id',
            'customers_emergency_address_id',
        ];

        if ($customer->update($request->only($fields))) {
            session()->flash('message', "The customer's default addresses have been updated.");
            return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $id]);
        }

        session()->flash('message', "The customer's default addresses were unable to be updated.");
        return redirect()->back();
    }

    /**
     * Process a quick order lookup by billing ID.
     * Redirects to order creation for the matched customer.
     */
    public function quickOrder(Request $request): RedirectResponse
    {
        return $this->processQuickOrder($request);
    }

    /**
     * Process a quick order submission.
     * Looks up a customer by billing ID and redirects to order add.
     */
    public function processQuickOrder(Request $request): RedirectResponse
    {
        $search = trim((string) $request->query('q', $request->input('q', '')));

        $customer = Customer::whereRaw('LOWER(billing_id) = ?', [mb_strtolower($search)])
            ->where('is_active', true)
            ->first();

        if ($customer) {
            return redirect()->route(auth('admin')->user()->role . '.orders.add', ['customerId' => $customer->customers_id]);
        }

        session()->flash('message', 'An active customer with Billing ID: "' . $search . '" was not found.');

        return redirect()->route(auth('admin')->user()->role . '.dashboard');
    }

    /**
     * Close a customer account (admin action).
     */
    public function closeAccount(int $customerId): RedirectResponse
    {
        $customer = Customer::findOrFail($customerId);

        if ($customer->closeAccount()) {
            session()->flash('message', "The customer's APO Box account has been closed.");
        } else {
            session()->flash('message', "There was a problem closing this customer's account.");
        }

        return redirect()->route(auth('admin')->user()->role . '.customers.view', ['id' => $customerId]);
    }

    /**
     * Show the demographics report.
     */
    public function demographicsReport(Request $request): View
    {
        $defaults = [
            'field' => 'entry_postcode',
            'limit' => 5,
            'from_date' => '2006-11-29',
            'to_date' => now()->format('Y-m-d'),
        ];

        $reportFields = [
            'entry_postcode' => 'Zip Code',
            'entry_state' => 'State',
            'entry_city' => 'City',
            'entry_country_id' => 'Country',
        ];

        $options = array_merge($defaults, $request->all());

        $data = collect();
        if ($request->isMethod('post') || $request->has('field')) {
            $field = $options['field'];
            $limit = (int) $options['limit'];
            $fromDate = $options['from_date'];
            $toDate = $options['to_date'];

            $data = Address::join('customers', 'address_book.customers_id', '=', 'customers.customers_id')
                ->join('customers_info', 'customers.customers_id', '=', 'customers_info.customers_info_id')
                ->where('customers.customers_shipping_address_id', '=', \DB::raw('address_book.address_book_id'))
                ->where('customers.is_active', true)
                ->when($fromDate, fn($q) => $q->where('customers_info.customers_info_date_account_created', '>=', $fromDate))
                ->when($toDate, fn($q) => $q->where('customers_info.customers_info_date_account_created', '<=', $toDate . ' 23:59:59'))
                ->select(\DB::raw("address_book.{$field} as label, COUNT(*) as total"))
                ->groupBy("address_book.{$field}")
                ->orderByDesc('total')
                ->limit($limit)
                ->get();
        }

        return view('manager.customers.demographics', compact('options', 'data', 'reportFields'));
    }

    /**
     * Create a Zendesk ticket for a customer.
     */
    public function createZendeskTicket(Request $request, int $customerId): RedirectResponse
    {
        $customer = Customer::findOrFail($customerId);
        $role = auth('admin')->user()->role;

        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $zendesk = app(ZendeskService::class);
        if (!$zendesk->isConfigured()) {
            session()->flash('message', 'Zendesk is not configured.');
            return redirect()->route($role . '.customers.view', ['id' => $customerId]);
        }

        $result = $zendesk->createTicketForCustomer(
            $customer,
            $request->input('subject'),
            $request->input('description')
        );

        if ($result && !empty($result['ticket_id'])) {
            session()->flash('message', sprintf('Zendesk ticket #%d created.', $result['ticket_id']));
        } elseif ($result && !empty($result['error'])) {
            session()->flash('error', $result['error']);
        } else {
            session()->flash('error', 'Failed to create Zendesk ticket — no customer email on file.');
        }

        return redirect()->route($role . '.customers.view', ['id' => $customerId]);
    }

    protected function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    protected function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
