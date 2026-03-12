@extends('layouts.default')
@section('title', 'My Account - APO Box')
@section('content')
<div class="customers account">
    <x-page-header title="My Account" />

    @if($requests->isNotEmpty())
        <x-table-card title="My Pending Requests">
            <div class="table-responsive">
                <table class="table table-modern">
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
        </x-table-card>
    @endif

    @if($awaitingPayments->isNotEmpty())
        <x-table-card title="My Orders Awaiting Payment">
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

    <x-table-card title="My Orders" :action="$showViewAllLink ? 'View All' : null" :action-url="$showViewAllLink ? url('/orders') : null">
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
                <div class="col-sm-6">
                    <x-address-card label="You Ship Packages Here:">
                        {{ $customer->customers_firstname }} {{ $customer->customers_lastname }}<br>
                        {{ $customer->billing_id }}<br>
                        {{ config('apobox.address.line1') }}<br>
                        {{ config('apobox.address.city') }}, {{ config('apobox.address.state') }} {{ config('apobox.address.zip') }}
                    </x-address-card>
                </div>
                <div class="col-sm-6">
                    <x-address-card label="We Forward Them Here:">
                        @if($customer->shippingAddress)
                            {{ $customer->shippingAddress->full }}
                        @endif
                    </x-address-card>
                </div>
            </div>
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
            <div class="row">
                <div class="col-sm-6">
                    <x-address-card label="Billing Address">
                        @if($customer->defaultAddress)
                            {{ $customer->defaultAddress->full }}
                        @endif
                    </x-address-card>
                </div>
                <div class="col-sm-6">
                    <x-address-card label="Shipping Address">
                        @if($customer->shippingAddress)
                            {{ $customer->shippingAddress->full }}
                        @endif
                    </x-address-card>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-sm-6">
                    <x-address-card label="Backup Shipping Address">
                        @if($customer->emergencyAddress)
                            {{ $customer->emergencyAddress->full }}
                        @endif
                    </x-address-card>
                </div>
                <div class="col-sm-6">
                    <x-detail-card title="All Addresses" action="New Address" :action-url="url('/address/add')">
                        @if($customer->addresses->isNotEmpty())
                            <ol class="mb-0">
                                @foreach($customer->addresses as $address)
                                    <li><a href="{{ url('/address/' . $address->address_book_id . '/edit') }}">{{ $address->full }}</a></li>
                                @endforeach
                            </ol>
                        @endif
                    </x-detail-card>
                </div>
            </div>
            <p class="mt-2"><small><a href="{{ url('/customers/edit/addresses') }}">Change default addresses</a></small></p>
        </div>

        {{-- Payment Tab --}}
        <div class="tab-pane" id="payment">
            <x-detail-card title="Payment Information" action="Update" :action-url="url('/customers/edit/payment_info')">
                <x-detail-row label="Name on Card">{{ $customer->cc_firstname }} {{ $customer->cc_lastname }}</x-detail-row>
                <x-detail-row label="Card Number">{{ $customer->cc_number }}</x-detail-row>
                <x-detail-row label="Expires">{{ $customer->cc_expires_month }}/20{{ $customer->cc_expires_year }}</x-detail-row>
            </x-detail-card>
        </div>

        {{-- Shipping Tab --}}
        <div class="tab-pane" id="shipping">
            <x-detail-card title="Shipping" action="Edit" :action-url="url('/customers/edit/shipping')">
                <x-detail-row label="Insurance Amount">${{ number_format($customer->insurance_amount ?? 0, 2) }}</x-detail-row>
                <x-detail-row label="Insurance Fee">${{ number_format($insuranceFee ?? 0, 2) }}</x-detail-row>
                <x-detail-row label="Default Postal Type">{{ config('apobox.postal_classes.' . $customer->default_postal_type, $customer->default_postal_type) }}</x-detail-row>
            </x-detail-card>
        </div>
    </div>
</div>
@endsection
