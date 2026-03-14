<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomerRequest;
use App\Mail\ConfirmClose;
use App\Models\Address;
use App\Models\Customer;
use App\Models\CustomersInfo;
use App\Models\CustomPackageRequest;
use App\Models\Insurance;
use App\Models\Zone;
use App\Services\CreditCardService;
use App\Services\ZendeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    /**
     * Show the customer account dashboard.
     *
     * If the customer has a partial signup (no billing ID), they are
     * redirected to the account-incomplete form instead.
     */
    public function account(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $customerId = $customer->customers_id;

        if (empty($customer->billing_id)) {
            return redirect('/customers/account-incomplete');
        }

        // Reload with relationships
        $customer = Customer::with([
            'addresses.zone',
            'defaultAddress.zone',
            'shippingAddress.zone',
            'emergencyAddress.zone',
            'authorizedNames',
        ])->findOrFail($customerId);

        $insuranceFee = Insurance::getFeeForCoverageAmount($customer->insurance_amount ?? 0);

        $orders = $customer->orders()
            ->with(['status', 'statusHistory', 'total'])
            ->orderByDesc('date_purchased')
            ->limit(5)
            ->get();

        $requests = CustomPackageRequest::where('customers_id', $customerId)
            ->active()
            ->get();

        $totalOrders = $customer->orders()->count();
        $showViewAllLink = $totalOrders > 5;

        $awaitingPayments = $customer->orders()
            ->where('orders_status', 2) // Awaiting Payment
            ->with('total')
            ->get();

        // Zendesk support tickets
        $zendesk = app(ZendeskService::class);
        $zendeskConfigured = $zendesk->isConfigured();
        $zendeskTickets = [];
        $zendeskError = null;

        if ($zendeskConfigured) {
            try {
                $zendeskTickets = $zendesk->getTicketsForEmail($customer->customers_email_address);
            } catch (\Exception $e) {
                $zendeskError = $e->getMessage();
            }
        }

        return view('customer.account', compact(
            'customer',
            'insuranceFee',
            'orders',
            'requests',
            'showViewAllLink',
            'awaitingPayments',
            'zendeskConfigured',
            'zendeskTickets',
            'zendeskError'
        ));
    }

    /**
     * Create a new Zendesk support ticket for the customer.
     */
    public function createTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $customer = Auth::guard('customer')->user();
        $zendesk = app(ZendeskService::class);

        $result = $zendesk->createTicketForCustomer($customer, $request->input('subject'), $request->input('description'));

        if ($result && !empty($result['ticket_id'])) {
            session()->flash('message', 'Your support ticket has been created.');
        } else {
            session()->flash('message', $result['error'] ?? 'There was a problem creating your ticket. Please try again.');
        }

        return redirect(route('customer.account') . '#support');
    }

    /**
     * Get comments for a Zendesk ticket (JSON).
     */
    public function ticketComments(int $id): JsonResponse
    {
        try {
            $zendesk = app(ZendeskService::class);

            if (!$zendesk->isConfigured()) {
                return response()->json(['error' => 'Zendesk is not configured.', 'comments' => []], 503);
            }

            $comments = $zendesk->getTicketComments($id);

            return response()->json(['comments' => $comments]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load comments: ' . $e->getMessage(), 'comments' => []], 500);
        }
    }

    /**
     * Add a reply to a Zendesk ticket (JSON).
     */
    public function replyToTicket(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $customer = Auth::guard('customer')->user();
        $zendesk = app(ZendeskService::class);

        $result = $zendesk->addCommentToTicket($id, $request->input('body'), $customer->customers_email_address);

        if ($result && !empty($result['success'])) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => $result['error'] ?? 'Failed to send reply.'], 422);
    }

    /**
     * Show edit form for a specific section of the customer profile.
     *
     * Supported partials: my_info, addresses, payment_info, shipping.
     */
    public function editPartial(string $partial): View|RedirectResponse
    {
        $validPartials = ['my_info', 'addresses', 'payment_info', 'shipping'];

        if (! in_array($partial, $validPartials)) {
            abort(404, 'The "' . $partial . '" group could not be found.');
        }

        $customer = Auth::guard('customer')->user();

        $fieldMap = [
            'my_info' => [
                'customers_email_address',
                'backup_email_address',
                'customers_telephone',
                'customers_fax',
            ],
            'addresses' => [
                'customers_default_address_id',
                'customers_shipping_address_id',
                'customers_emergency_address_id',
            ],
            'payment_info' => [
                'cc_firstname',
                'cc_lastname',
                'cc_number',
                'cc_expires_month',
                'cc_expires_year',
                'cc_cvv',
            ],
            'shipping' => [
                'insurance_amount',
                'default_postal_type',
            ],
        ];

        $fields = $fieldMap[$partial];

        // Extra view data depending on partial
        $extraData = [];

        switch ($partial) {
            case 'addresses':
                $addresses = $customer->addresses()
                    ->get()
                    ->pluck('full', 'address_book_id');
                $extraData['addresses'] = $addresses;
                break;

            case 'payment_info':
                $extraData['months'] = array_combine(range(1, 12), array_map(
                    fn ($m) => str_pad($m, 2, '0', STR_PAD_LEFT),
                    range(1, 12)
                ));
                $currentYear = (int) date('y');
                $years = [];
                for ($i = $currentYear; $i <= $currentYear + 10; $i++) {
                    $years[$i] = '20' . str_pad($i, 2, '0', STR_PAD_LEFT);
                }
                $extraData['years'] = $years;
                // Do not pass existing card data to the form
                $customer = clone $customer;
                $customer->cc_number = null;
                $customer->cc_expires_month = null;
                $customer->cc_expires_year = null;
                $customer->cc_cvv = null;
                break;

            case 'shipping':
                $extraData['postalClasses'] = config('apobox.postal_classes');
                break;
        }

        return view('customer.edit-partial', array_merge(
            compact('partial', 'customer', 'fields'),
            $extraData
        ));
    }

    /**
     * Handle profile updates from partial edit forms.
     *
     * The partial name comes from the route parameter {partial}.
     */
    public function update(UpdateCustomerRequest $request, string $partial = 'my_info'): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $data = $request->validated();

        // Handle special logic per partial
        switch ($partial) {
            case 'shipping':
                if (isset($data['insurance_amount'])) {
                    $fee = Insurance::getFeeForCoverageAmount($data['insurance_amount']);
                    $data['insurance_fee'] = $fee;
                }
                break;

            case 'payment_info':
                // Encrypt and store credit card
                if (! empty($data['cc_number'])) {
                    $data['cc_number_encrypted'] = $customer->encryptCreditCard($data['cc_number']);
                    $data['cc_number'] = $customer->maskCardNumber($data['cc_number']);
                }
                break;
        }

        $customer->fill($data);

        if ($customer->save()) {
            // Record edit activity
            $info = CustomersInfo::firstOrCreate(
                ['customers_info_id' => $customer->customers_id],
            );
            $activityData = [
                'customers_info_date_account_last_modified' => now(),
            ];
            if ($partial === 'payment_info') {
                $activityData['IP_cc_update'] = $request->ip();
            }
            if ($partial === 'addresses') {
                $activityData['IP_addressbook_update'] = $request->ip();
            }
            $info->update($activityData);

            session()->flash('message', 'The information has been updated.');

            return redirect(route('customer.account') . '#' . str_replace('_', '-', $partial));
        }

        session()->flash('message', 'The information could not be updated. Please try again.');

        return redirect()->back()->withInput();
    }

    /**
     * Show the change password form.
     */
    public function changePassword(): View
    {
        return view('customer.change-password');
    }

    /**
     * Handle password update.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
            'confirm_new_password' => ['required', 'string'],
        ]);

        $customer = Auth::guard('customer')->user();

        // Verify current password
        if (! Hash::check($request->input('current_password'), $customer->customers_password)) {
            session()->flash('message', 'Your current password was incorrect and was not updated.');
            return redirect()->back();
        }

        // Verify new passwords match
        if ($request->input('new_password') !== $request->input('confirm_new_password')) {
            session()->flash('message', 'Your new password did not match the confirmation.');
            return redirect()->back();
        }

        $customer->customers_password = $request->input('new_password');

        if ($customer->save()) {
            // Re-authenticate with new password
            Auth::guard('customer')->login($customer);
            session()->flash('message', 'Your password has been successfully changed.');
            return redirect()->route('customer.account');
        }

        session()->flash('message', 'Your password could not be changed.');
        return redirect()->back();
    }

    /**
     * Show the account incomplete page.
     *
     * This handles customers who registered but never completed the second
     * step (adding a shipping address). On POST it saves the address and
     * links it to the customer.
     */
    public function accountIncomplete(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $customerId = $customer->customers_id;

        // If the customer already has a billing_id, their account is complete
        if (! empty($customer->billing_id)) {
            return redirect()->route('customer.account');
        }

        $zones = Zone::where('zone_country_id', 223)
            ->pluck('zone_name', 'zone_id');

        if (request()->isMethod('post')) {
            $validated = request()->validate([
                'entry_firstname' => ['required', 'string', 'max:64'],
                'entry_lastname' => ['required', 'string', 'max:64'],
                'entry_street_address' => ['required', 'string', 'max:255'],
                'entry_suburb' => ['nullable', 'string', 'max:64'],
                'entry_city' => ['required', 'string', 'max:64'],
                'entry_state' => ['nullable', 'string', 'max:32'],
                'entry_postcode' => ['required', 'string', 'max:10'],
                'entry_country_id' => ['required', 'integer'],
                'entry_zone_id' => ['nullable', 'integer'],
            ]);

            $validated['customers_id'] = $customerId;

            $address = Address::create($validated);

            if ($address) {
                $updated = Customer::where('customers_id', $customerId)->update([
                    'customers_default_address_id' => $address->address_book_id,
                    'customers_shipping_address_id' => $address->address_book_id,
                ]);

                if ($updated) {
                    session()->flash('message', 'Your account is now complete!');
                    return redirect()->route('customer.account');
                }

                // Rollback address on failure
                $address->delete();
            }

            session()->flash('message', 'There were errors with your input, please try again.');
        }

        return view('customer.account-incomplete', [
            'zones' => $zones,
            'customer' => $customer,
        ]);
    }

    /**
     * Show the almost finished page (post-registration).
     *
     * This is the billing/credit card completion step for customers who
     * signed up via the widget and still need billing info.
     */
    public function almostFinished(): View|RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        $customerId = $customer->customers_id;

        // Check if the customer actually needs to complete billing
        $customer = Customer::where('customers_id', $customerId)
            ->where(function ($q) {
                $q->where('billing_id', '')->orWhereNull('billing_id');
            })
            ->first();

        if (! $customer) {
            session()->flash('message', 'Your account is complete.');
            return redirect()->route('customer.account');
        }

        $addresses = Address::where('customers_id', $customerId)
            ->get()
            ->pluck('full', 'address_book_id');

        $zones = Zone::where('zone_country_id', 223)
            ->pluck('zone_name', 'zone_id');

        $sources = config('apobox.customers.sources', []);

        if (request()->isMethod('post') || request()->isMethod('put')) {
            $validated = request()->validate([
                'customers_default_address_id' => ['required'],
                'cc_firstname' => ['required', 'string', 'max:64'],
                'cc_lastname' => ['required', 'string', 'max:64'],
                'cc_number' => ['required', 'string', 'max:20'],
                'cc_expires_month' => ['required', 'string', 'max:2'],
                'cc_expires_year' => ['required', 'string', 'max:2'],
                'cc_cvv' => ['required', 'string', 'max:4'],
                'source_id' => ['nullable', 'integer'],
            ]);

            $addressId = $validated['customers_default_address_id'];

            // If user selected "new", create a new address from the address form data
            if ($addressId === 'new') {
                $addressData = request()->validate([
                    'entry_firstname' => ['required', 'string', 'max:64'],
                    'entry_lastname' => ['required', 'string', 'max:64'],
                    'entry_street_address' => ['required', 'string', 'max:255'],
                    'entry_suburb' => ['nullable', 'string', 'max:64'],
                    'entry_city' => ['required', 'string', 'max:64'],
                    'entry_state' => ['nullable', 'string', 'max:32'],
                    'entry_postcode' => ['required', 'string', 'max:10'],
                    'entry_country_id' => ['required', 'integer'],
                    'entry_zone_id' => ['nullable', 'integer'],
                ]);

                $addressData['customers_id'] = $customerId;
                $newAddress = Address::create($addressData);

                if (! $newAddress) {
                    session()->flash('message', 'There were errors with your input, please try again.');
                    return redirect()->back()->withInput();
                }

                $addressId = $newAddress->address_book_id;
            }

            // Encrypt and mask credit card
            $ccEncrypted = $customer->encryptCreditCard($validated['cc_number']);
            $ccMasked = $customer->maskCardNumber($validated['cc_number']);

            $customerData = [
                'customers_default_address_id' => $addressId,
                'cc_firstname' => $validated['cc_firstname'],
                'cc_lastname' => $validated['cc_lastname'],
                'cc_number' => $ccMasked,
                'cc_number_encrypted' => $ccEncrypted,
                'cc_expires_month' => $validated['cc_expires_month'],
                'cc_expires_year' => $validated['cc_expires_year'],
                'cc_cvv' => $validated['cc_cvv'],
            ];

            if ($customer->update($customerData)) {
                // Record source activity if provided
                if (! empty($validated['source_id'])) {
                    $info = CustomersInfo::firstOrCreate(
                        ['customers_info_id' => $customerId],
                    );
                    $info->update([
                        'customers_info_source_id' => $validated['source_id'],
                    ]);
                }

                session()->flash('message', 'Your account is now complete!');
                return redirect()->route('customer.account');
            }

            // Clean up the newly created address if the customer save failed
            if (isset($newAddress)) {
                $newAddress->delete();
            }

            session()->flash('message', 'There were errors with your input, please try again.');
        }

        // Pre-fill form data
        $customer->cc_cvv = '';

        return view('customer.almost-finished', compact(
            'customer',
            'addresses',
            'zones',
            'sources'
        ));
    }

    /**
     * Handle close account request (from email confirmation link).
     *
     * The hash is a SHA1 of today's date + customer ID. This ensures
     * the link is only valid for the day it was generated.
     */
    public function closeAccount(string $hash): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if (! $customer) {
            session()->flash('message', 'You must be logged in to close your account.');
            return redirect()->route('login');
        }

        $expectedHash = sha1(date('Y-m-d') . $customer->customers_id);

        if ($hash !== $expectedHash) {
            session()->flash('message', 'There was a problem closing your account.');
            return redirect(route('customer.account') . '#my-info');
        }

        if ($customer->closeAccount()) {
            // Record close activity
            $info = CustomersInfo::firstOrCreate(
                ['customers_info_id' => $customer->customers_id],
            );
            $info->update([
                'customers_info_date_account_closed' => now(),
            ]);

            Auth::guard('customer')->logout();
            session()->invalidate();
            session()->regenerateToken();

            session()->flash('message', 'Your APO Box address has been deactivated and your credit card information has been removed from our system. We will no longer forward packages on your behalf.');

            return redirect('/');
        }

        session()->flash('message', 'There was a problem closing your account.');

        return redirect(route('customer.account') . '#my-info');
    }

    /**
     * Confirm and process account closure.
     *
     * This is an AJAX endpoint that sends the customer a confirmation email
     * containing a link to actually close their account.
     */
    public function confirmClose(int $customerId, string $hash): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        // Verify the hash matches the customer
        $expectedHash = sha1(date('Y-m-d') . $customer->customers_id);

        if ($hash !== $expectedHash || $customer->customers_id !== $customerId) {
            session()->flash('message', 'There was a problem verifying your request.');
            return redirect(route('customer.account') . '#my-info');
        }

        $closeUrl = url('/close-account/' . $hash);

        try {
            Mail::to($customer->customers_email_address)
                ->send(new ConfirmClose($customer->full_name, $closeUrl));

            session()->flash('message', 'A confirmation email has been sent to your email address.');
        } catch (\Exception $e) {
            Log::error('Failed to send close account confirmation: ' . $e->getMessage());
            session()->flash('message', 'There was a problem sending the confirmation email.');
        }

        return redirect(route('customer.account') . '#my-info');
    }
}
