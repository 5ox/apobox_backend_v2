@extends('layouts.default')
@section('title', 'My Account - APO Box')
@section('content')
<div class="customers account">
    <x-page-header title="My Account" />

    {{-- Account Tabs --}}
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account-tab" type="button">My Account</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#my-info" type="button">My Info</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#authorized_names" type="button">Authorized Names</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#addresses" type="button">Addresses</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#payment" type="button">Payment</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#insurance" type="button">Insurance</button></li>
    </ul>
    <div class="tab-content mt-3">
        {{-- Account Tab --}}
        <div class="tab-pane active" id="account-tab">
            <div class="row">
                <div class="col-sm-6">
                    <div class="address-card border-primary bg-primary bg-opacity-10 h-100">
                        <div class="address-card__label text-primary">
                            <span><i data-lucide="warehouse" class="icon--sm me-1"></i> You Ship Packages Here</span>
                        </div>
                        <address class="mb-0 fw-semibold">
                            {{ $customer->customers_firstname }} {{ $customer->customers_lastname }}<br>
                            {{ $customer->billing_id }}<br>
                            {{ config('apobox.address.line1') }}<br>
                            {{ config('apobox.address.city') }}, {{ config('apobox.address.state') }} {{ config('apobox.address.zip') }}
                        </address>
                        <div class="form-text mt-2">Ship your packages to this address. Include your Billing ID so we can identify them when they arrive.</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="address-card border-success bg-success bg-opacity-10 h-100">
                        <div class="address-card__label text-success">
                            <span><i data-lucide="truck" class="icon--sm me-1"></i> We Forward Them Here</span>
                        </div>
                        @if($customer->shippingAddress)
                            <address class="mb-0 fw-semibold">
                                {{ $customer->shippingAddress->entry_firstname }} {{ $customer->shippingAddress->entry_lastname }}<br>
                                @if($customer->shippingAddress->entry_company){{ $customer->shippingAddress->entry_company }}<br>@endif
                                {{ $customer->shippingAddress->entry_street_address }}<br>
                                @if($customer->shippingAddress->entry_suburb){{ $customer->shippingAddress->entry_suburb }}<br>@endif
                                {{ $customer->shippingAddress->entry_city }}, {{ $customer->shippingAddress->zone?->zone_code ?? $customer->shippingAddress->entry_state }} {{ $customer->shippingAddress->entry_postcode }}
                            </address>
                        @else
                            <p class="text-muted mb-0 small">Not set</p>
                        @endif
                        <div class="form-text mt-2">This is where we forward your packages after processing.</div>
                    </div>
                </div>
            </div>

            @if($awaitingPayments->isNotEmpty())
                <x-table-card title="My Orders Awaiting Payment" class="mt-4">
                    <p class="px-3 pt-2 text-muted small">The orders listed below were unable to be automatically paid. These orders will need to be paid manually before they can be shipped.</p>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead><tr><th></th><th>Order #</th><th>Total</th><th>Tracking #</th><th>Dimensions</th><th>Weight</th><th>Date Received</th></tr></thead>
                            <tbody>
                                @foreach($awaitingPayments as $order)
                                    <tr>
                                        <td><a href="{{ url('/orders/' . $order->orders_id . '/pay') }}" class="btn btn-primary btn-sm">Pay</a></td>
                                        <td><a href="{{ url('/orders/' . $order->orders_id) }}">{{ $order->orders_id }}</a></td>
                                        <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                                        <td>{{ $order->inbound_tracking }}</td>
                                        <td>{{ $order->dimensions }}</td>
                                        <td>{{ $order->weight ? $order->weight . ' lb' : 'N/A' }}</td>
                                        <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-table-card>
            @endif

            <x-table-card title="Custom Package Requests" class="mt-4"
                          action="New Request" :action-url="url('/requests/add')">
                @if($requests->isEmpty())
                    <p class="px-3 py-3 text-muted mb-0">No active custom package requests.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Tracking #</th>
                                    <th>Services</th>
                                    <th>Instructions</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td class="text-nowrap">{{ $request->order_add_date?->format('m/d/Y') }}</td>
                                        <td>
                                            @if($request->tracking_id && $request->tracking_id !== '0')
                                                <code>{{ $request->tracking_id }}</code>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->package_repack === 'yes')
                                                <span class="badge bg-info-subtle text-info">Repack</span>
                                            @endif
                                            @if($request->insurance_coverage)
                                                <span class="badge bg-warning-subtle text-warning">${{ $request->insurance_coverage }} Ins.</span>
                                            @endif
                                            @if($request->package_repack !== 'yes' && !$request->insurance_coverage)
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($request->instructions, 60) }}</td>
                                        <td><x-status-badge :status="$request->status_label ?? 'Unknown'" /></td>
                                        <td class="text-nowrap">
                                            <a href="{{ url('/requests/edit/' . $request->custom_orders_id) }}" class="btn btn-sm btn-outline-primary">
                                                <i data-lucide="pencil" class="icon--sm"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-table-card>

            <x-table-card title="My Orders" class="mt-4" :action="$showViewAllLink ? 'View All' : null" :action-url="$showViewAllLink ? url('/orders') : null">
                @if($orders->isEmpty())
                    <p class="px-3 py-3 text-muted mb-0">You have no orders at this time.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead><tr><th>Order #</th><th>Outbound Tracking</th><th>Inbound Tracking</th><th>Status</th><th>Postage Class</th><th>Date Shipped</th><th>Date Processed</th><th>Total</th></tr></thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td><a href="{{ url('/orders/' . $order->orders_id) }}">{{ $order->orders_id }}</a></td>
                                        <td>{{ $order->usps_track_num }}</td>
                                        <td>{{ $order->inbound_tracking }}</td>
                                        <td><x-status-badge :status="$order->status?->orders_status_name" /></td>
                                        <td>{{ $order->mail_class }}</td>
                                        <td>{{ $order->date_shipped?->format('m/d/Y') }}</td>
                                        <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                                        <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-table-card>
        </div>

        {{-- My Info Tab --}}
        <div class="tab-pane" id="my-info">
            <x-detail-card title="My Information" action="Edit" :action-url="url('/customers/edit/my_info')">
                <x-detail-row label="Billing ID">{{ $customer->billing_id }}</x-detail-row>
                <x-detail-row label="First Name">{{ $customer->customers_firstname }}</x-detail-row>
                <x-detail-row label="Last Name">{{ $customer->customers_lastname }}</x-detail-row>
                <x-detail-row label="Email">{{ $customer->customers_email_address }}</x-detail-row>
                <x-detail-row label="Backup Email">{{ $customer->backup_email_address }}</x-detail-row>
                <x-detail-row label="Telephone">{{ $customer->customers_telephone }}</x-detail-row>
                <x-detail-row label="Cell Phone">{{ $customer->customers_fax }}</x-detail-row>
                <x-detail-row label="Password"><a href="{{ url('/customers/change-password') }}">Change</a></x-detail-row>
            </x-detail-card>
            <div class="mt-3">
                <a href="{{ url('/close-account/' . sha1(date('Y-m-d') . $customer->customers_id)) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to close your account?')">Close Account</a>
            </div>
        </div>

        {{-- Authorized Names Tab --}}
        <div class="tab-pane" id="authorized_names">
            <x-table-card title="Authorized Names" action="Add" :action-url="url('/authorized_names/add')">
                @if($customer->authorizedNames->isNotEmpty())
                    <table class="table table-modern">
                        <thead><tr><th>Name</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach($customer->authorizedNames as $name)
                                <tr>
                                    <td>{{ $name->authorized_firstname }} {{ $name->authorized_lastname }}</td>
                                    <td>
                                        <a href="{{ url('/authorized_names/' . $name->authorized_names_id . '/edit') }}">Edit</a> |
                                        <a href="{{ url('/authorized_names/' . $name->authorized_names_id . '/delete') }}" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="px-3 py-3 text-muted mb-0">You haven't created any authorized names. <a href="{{ url('/authorized_names/add') }}">Add one</a></p>
                @endif
            </x-table-card>
        </div>

        {{-- Addresses Tab --}}
        <div class="tab-pane" id="addresses">
            {{-- Warehouse / Ship-To Address --}}
            <div class="row mb-4">
                <div class="col-sm-6">
                    <div class="address-card border-primary bg-primary bg-opacity-10 h-100">
                        <div class="address-card__label text-primary">
                            <span><i data-lucide="warehouse" class="icon--sm me-1"></i> You Ship Packages Here</span>
                        </div>
                        <address class="mb-0 fw-semibold">
                            {{ $customer->customers_firstname }} {{ $customer->customers_lastname }}<br>
                            {{ $customer->billing_id }}<br>
                            {{ config('apobox.address.line1') }}<br>
                            {{ config('apobox.address.city') }}, {{ config('apobox.address.state') }} {{ config('apobox.address.zip') }}
                        </address>
                        <div class="form-text mt-2">Ship your packages to this address. Include your Billing ID so we can identify them when they arrive.</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="address-card border-success bg-success bg-opacity-10 h-100">
                        <div class="address-card__label text-success">
                            <span><i data-lucide="truck" class="icon--sm me-1"></i> We Forward Them Here</span>
                        </div>
                        @if($customer->shippingAddress)
                            <address class="mb-0 fw-semibold">
                                {{ $customer->shippingAddress->entry_firstname }} {{ $customer->shippingAddress->entry_lastname }}<br>
                                @if($customer->shippingAddress->entry_company){{ $customer->shippingAddress->entry_company }}<br>@endif
                                {{ $customer->shippingAddress->entry_street_address }}<br>
                                @if($customer->shippingAddress->entry_suburb){{ $customer->shippingAddress->entry_suburb }}<br>@endif
                                {{ $customer->shippingAddress->entry_city }}, {{ $customer->shippingAddress->zone?->zone_code ?? $customer->shippingAddress->entry_state }} {{ $customer->shippingAddress->entry_postcode }}
                            </address>
                        @else
                            <p class="text-muted mb-0 small">Not set</p>
                        @endif
                        <div class="form-text mt-2">This is where we forward your packages after processing.</div>
                    </div>
                </div>
            </div>

            {{-- Default Address Assignments --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Default Addresses</h6>
                <a href="{{ url('/customers/edit/addresses') }}" class="btn btn-sm btn-outline-secondary"><i data-lucide="settings" class="icon--sm me-1"></i>Change Defaults</a>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <x-address-card label="Billing Address" :edit-url="$customer->defaultAddress ? url('/address/' . $customer->defaultAddress->address_book_id . '/edit') : null">
                        @if($customer->defaultAddress)
                            {{ $customer->defaultAddress->entry_firstname }} {{ $customer->defaultAddress->entry_lastname }}<br>
                            @if($customer->defaultAddress->entry_company){{ $customer->defaultAddress->entry_company }}<br>@endif
                            {{ $customer->defaultAddress->entry_street_address }}<br>
                            @if($customer->defaultAddress->entry_suburb){{ $customer->defaultAddress->entry_suburb }}<br>@endif
                            {{ $customer->defaultAddress->entry_city }}, {{ $customer->defaultAddress->zone?->zone_code ?? $customer->defaultAddress->entry_state }} {{ $customer->defaultAddress->entry_postcode }}
                        @endif
                    </x-address-card>
                </div>
                <div class="col-md-4">
                    <x-address-card label="Shipping Address" :edit-url="$customer->shippingAddress ? url('/address/' . $customer->shippingAddress->address_book_id . '/edit') : null">
                        @if($customer->shippingAddress)
                            {{ $customer->shippingAddress->entry_firstname }} {{ $customer->shippingAddress->entry_lastname }}<br>
                            @if($customer->shippingAddress->entry_company){{ $customer->shippingAddress->entry_company }}<br>@endif
                            {{ $customer->shippingAddress->entry_street_address }}<br>
                            @if($customer->shippingAddress->entry_suburb){{ $customer->shippingAddress->entry_suburb }}<br>@endif
                            {{ $customer->shippingAddress->entry_city }}, {{ $customer->shippingAddress->zone?->zone_code ?? $customer->shippingAddress->entry_state }} {{ $customer->shippingAddress->entry_postcode }}
                        @endif
                    </x-address-card>
                </div>
                <div class="col-md-4">
                    <x-address-card label="Backup Shipping Address" :edit-url="$customer->emergencyAddress ? url('/address/' . $customer->emergencyAddress->address_book_id . '/edit') : null">
                        @if($customer->emergencyAddress)
                            {{ $customer->emergencyAddress->entry_firstname }} {{ $customer->emergencyAddress->entry_lastname }}<br>
                            @if($customer->emergencyAddress->entry_company){{ $customer->emergencyAddress->entry_company }}<br>@endif
                            {{ $customer->emergencyAddress->entry_street_address }}<br>
                            @if($customer->emergencyAddress->entry_suburb){{ $customer->emergencyAddress->entry_suburb }}<br>@endif
                            {{ $customer->emergencyAddress->entry_city }}, {{ $customer->emergencyAddress->zone?->zone_code ?? $customer->emergencyAddress->entry_state }} {{ $customer->emergencyAddress->entry_postcode }}
                        @endif
                    </x-address-card>
                </div>
            </div>

            {{-- All Addresses --}}
            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                <h6 class="mb-0">All Addresses</h6>
                <a href="{{ url('/address/add') }}" class="btn btn-sm btn-primary"><i data-lucide="plus" class="icon--sm me-1"></i>New Address</a>
            </div>
            @if($customer->addresses->isNotEmpty())
                <div class="row">
                    @foreach($customer->addresses as $address)
                        @php
                            $isBilling = $customer->customers_default_address_id == $address->address_book_id;
                            $isShipping = $customer->customers_shipping_address_id == $address->address_book_id;
                            $isEmergency = $customer->customers_emergency_address_id == $address->address_book_id;
                            $isDefault = $isBilling || $isShipping || $isEmergency;
                        @endphp
                        <div class="col-md-4 mb-3">
                            <div class="address-card h-100">
                                <div class="address-card__label">
                                    <span>
                                        @if($isBilling)<span class="badge bg-primary-subtle text-primary me-1">Billing</span>@endif
                                        @if($isShipping)<span class="badge bg-success-subtle text-success me-1">Shipping</span>@endif
                                        @if($isEmergency)<span class="badge bg-warning-subtle text-warning me-1">Backup</span>@endif
                                        @if(!$isDefault)<span class="text-muted">Address</span>@endif
                                    </span>
                                </div>
                                <address class="mb-2">
                                    {{ $address->entry_firstname }} {{ $address->entry_lastname }}<br>
                                    @if($address->entry_company){{ $address->entry_company }}<br>@endif
                                    {{ $address->entry_street_address }}<br>
                                    @if($address->entry_suburb){{ $address->entry_suburb }}<br>@endif
                                    {{ $address->entry_city }}, {{ $address->zone?->zone_code ?? $address->entry_state }} {{ $address->entry_postcode }}
                                </address>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('/address/' . $address->address_book_id . '/edit') }}" class="btn btn-sm btn-outline-primary">
                                        <i data-lucide="pencil" class="icon--sm"></i> Edit
                                    </a>
                                    @unless($isDefault)
                                        <a href="{{ url('/address/' . $address->address_book_id . '/delete') }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this address?')">
                                            <i data-lucide="trash-2" class="icon--sm"></i> Delete
                                        </a>
                                    @endunless
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No addresses on file. <a href="{{ url('/address/add') }}">Add your first address</a>.</p>
            @endif
        </div>

        {{-- Payment Tab --}}
        <div class="tab-pane" id="payment">
            <x-detail-card title="Payment Information" action="Update" :action-url="url('/customers/edit/payment_info')">
                <x-detail-row label="Name on Card">{{ $customer->cc_firstname }} {{ $customer->cc_lastname }}</x-detail-row>
                <x-detail-row label="Card Number">{{ $customer->cc_number }}</x-detail-row>
                <x-detail-row label="Expires">{{ $customer->cc_expires_month }}/20{{ $customer->cc_expires_year }}</x-detail-row>
            </x-detail-card>
        </div>

        {{-- Insurance Tab --}}
        <div class="tab-pane" id="insurance">
            <x-detail-card title="Insurance" action="Edit" :action-url="url('/customers/edit/shipping')">
                <x-detail-row label="Insurance Amount">${{ number_format($customer->insurance_amount ?? 0, 2) }}</x-detail-row>
                <x-detail-row label="Insurance Fee">${{ number_format($insuranceFee ?? 0, 2) }}</x-detail-row>
                <x-detail-row label="Default Postal Type">{{ config('apobox.postal_classes.' . $customer->default_postal_type, $customer->default_postal_type) }}</x-detail-row>
            </x-detail-card>
        </div>

    </div>

</div>

@endsection
