@extends('layouts.default')
@section('title', 'My Account - APO Box')
@section('content')
<div class="customers account">
    @if($requests->isNotEmpty())
        <h3>My Pending Requests</h3>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Date</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->order_add_date?->format('m/d/Y') }}</td>
                            <td>{{ $request->instructions }}</td>
                            <td>{{ $request->status_label }}</td>
                            <td><a href="{{ url('/requests/edit/' . $request->custom_orders_id) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($awaitingPayments->isNotEmpty())
        <h3>My Orders Awaiting Payment</h3>
        <p>The orders listed below were unable to be automatically paid. These orders will need to be paid manually before they can be shipped.</p>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th></th><th>Order #</th><th>Total</th><th>Tracking #</th><th>Dimensions</th><th>Weight</th><th>Date Received</th></tr></thead>
                <tbody>
                    @foreach($awaitingPayments as $order)
                        <tr>
                            <td><a href="{{ url('/orders/' . $order->orders_id . '/pay') }}" class="btn btn-primary btn-sm">Pay</a></td>
                            <td><a href="{{ url('/orders/' . $order->orders_id) }}">{{ $order->orders_id }}</a></td>
                            <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                            <td>{{ $order->inbound_tracking }}</td>
                            <td>{{ $order->dimensions }}</td>
                            <td>{{ $order->weight }} lb</td>
                            <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <h3>
        My Orders
        @if($showViewAllLink)
            <small><a href="{{ url('/orders') }}">View All</a></small>
        @endif
    </h3>
    @if($orders->isEmpty())
        <p>You have no orders at this time.</p>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead><tr><th>Order #</th><th>Outbound Tracking</th><th>Inbound Tracking</th><th>Status</th><th>Postage Class</th><th>Date Shipped</th><th>Date Processed</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><a href="{{ url('/orders/' . $order->orders_id) }}">{{ $order->orders_id }}</a></td>
                            <td>{{ $order->usps_track_num }}</td>
                            <td>{{ $order->inbound_tracking }}</td>
                            <td><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></td>
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

    {{-- Account Tabs --}}
    <ul class="nav nav-tabs mt-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#account-tab" type="button">My Account</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#my-info" type="button">My Info</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#authorized_names" type="button">Authorized Names</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#addresses" type="button">Addresses</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#payment" type="button">Payment</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#shipping" type="button">Shipping</button></li>
    </ul>
    <div class="tab-content mt-3">
        {{-- Account Tab --}}
        <div class="tab-pane active" id="account-tab">
            <div class="row">
                <div class="col-sm-4">
                    <h4>You Ship Packages Here:</h4>
                    <div class="bg-light p-3 rounded">
                        <address class="lead mb-0">
                            {{ $customer->customers_firstname }} {{ $customer->customers_lastname }}<br>
                            {{ $customer->billing_id }}<br>
                            {{ config('apobox.address.line1') }}<br>
                            {{ config('apobox.address.city') }}, {{ config('apobox.address.state') }} {{ config('apobox.address.zip') }}
                        </address>
                    </div>
                </div>
                <div class="col-sm-4">
                    <h4>We Forward Them Here:</h4>
                    <div class="bg-light p-3 rounded">
                        @if($customer->shippingAddress)
                            <address class="mb-0">{{ $customer->shippingAddress->full }}</address>
                        @else
                            <p class="text-muted">No shipping address set</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- My Info Tab --}}
        <div class="tab-pane" id="my-info">
            <div class="row">
                <div class="col-sm-7">
                    <h4>My Information <small><a href="{{ url('/customers/edit/my_info') }}">Edit</a></small></h4>
                    <dl class="row">
                        <dt class="col-sm-4">Billing ID</dt><dd class="col-sm-8">{{ $customer->billing_id }}</dd>
                        <dt class="col-sm-4">First Name</dt><dd class="col-sm-8">{{ $customer->customers_firstname }}</dd>
                        <dt class="col-sm-4">Last Name</dt><dd class="col-sm-8">{{ $customer->customers_lastname }}</dd>
                        <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $customer->customers_email_address }}</dd>
                        <dt class="col-sm-4">Backup Email</dt><dd class="col-sm-8">{{ $customer->backup_email_address }}</dd>
                        <dt class="col-sm-4">Telephone</dt><dd class="col-sm-8">{{ $customer->customers_telephone }}</dd>
                        <dt class="col-sm-4">Cell Phone</dt><dd class="col-sm-8">{{ $customer->customers_fax }}</dd>
                        <dt class="col-sm-4">Password</dt><dd class="col-sm-8"><a href="{{ url('/customers/change-password') }}">Change</a></dd>
                    </dl>
                </div>
            </div>
            <a href="{{ url('/close-account/' . sha1(date('Y-m-d') . $customer->customers_id)) }}" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to close your account?')">Close Account</a>
        </div>

        {{-- Authorized Names Tab --}}
        <div class="tab-pane" id="authorized_names">
            <h4>Authorized Names <small><a href="{{ url('/authorized_names/add') }}">Add</a></small></h4>
            @if($customer->authorizedNames->isNotEmpty())
                <table class="table table-sm table-striped">
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
                <p>You haven't created any authorized names. <a href="{{ url('/authorized_names/add') }}">Add one</a></p>
            @endif
        </div>

        {{-- Addresses Tab --}}
        <div class="tab-pane" id="addresses">
            <div class="row">
                <div class="col-sm-6">
                    <h4>Billing Address</h4>
                    <div class="bg-light p-3 rounded">
                        @if($customer->defaultAddress)
                            <address class="mb-0">{{ $customer->defaultAddress->full }}</address>
                        @else
                            <p class="text-muted">Not set</p>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <h4>Shipping Address</h4>
                    <div class="bg-light p-3 rounded">
                        @if($customer->shippingAddress)
                            <address class="mb-0">{{ $customer->shippingAddress->full }}</address>
                        @else
                            <p class="text-muted">Not set</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-sm-6">
                    <h4>Backup Shipping Address</h4>
                    <div class="bg-light p-3 rounded">
                        @if($customer->emergencyAddress)
                            <address class="mb-0">{{ $customer->emergencyAddress->full }}</address>
                        @else
                            <p class="text-muted">Not set</p>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <h4>All Addresses</h4>
                    @if($customer->addresses->isNotEmpty())
                        <ol>
                            @foreach($customer->addresses as $address)
                                <li><a href="{{ url('/address/' . $address->address_book_id . '/edit') }}">{{ $address->full }}</a></li>
                            @endforeach
                        </ol>
                        <a href="{{ url('/address/add') }}">New Address</a>
                    @endif
                </div>
            </div>
            <p class="mt-2"><small><a href="{{ url('/customers/edit/addresses') }}">Change default addresses</a></small></p>
        </div>

        {{-- Payment Tab --}}
        <div class="tab-pane" id="payment">
            <h4>Payment Information <small><a href="{{ url('/customers/edit/payment_info') }}">Update</a></small></h4>
            <dl class="row">
                <dt class="col-sm-3">Name on Card</dt><dd class="col-sm-9">{{ $customer->cc_firstname }} {{ $customer->cc_lastname }}</dd>
                <dt class="col-sm-3">Card Number</dt><dd class="col-sm-9">{{ $customer->cc_number }}</dd>
                <dt class="col-sm-3">Expires</dt><dd class="col-sm-9">{{ $customer->cc_expires_month }}/20{{ $customer->cc_expires_year }}</dd>
            </dl>
        </div>

        {{-- Shipping Tab --}}
        <div class="tab-pane" id="shipping">
            <h4>Shipping <small><a href="{{ url('/customers/edit/shipping') }}">Edit</a></small></h4>
            <dl class="row">
                <dt class="col-sm-3">Insurance Amount</dt><dd class="col-sm-9">${{ number_format($customer->insurance_amount ?? 0, 2) }}</dd>
                <dt class="col-sm-3">Insurance Fee</dt><dd class="col-sm-9">${{ number_format($insuranceFee ?? 0, 2) }}</dd>
                <dt class="col-sm-3">Default Postal Type</dt><dd class="col-sm-9">{{ config('apobox.postal_classes.' . $customer->default_postal_type, $customer->default_postal_type) }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
