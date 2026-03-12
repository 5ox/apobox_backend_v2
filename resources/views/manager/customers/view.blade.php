@extends('layouts.manager')
@section('title', $customer->full_name . ' - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>{{ $customer->full_name }} @if($closed) <span class="badge bg-danger">Closed {{ $closed }}</span> @endif</h2>
@if($partialSignup) <div class="alert alert-warning">Partial signup - no billing ID</div> @endif

<div class="row">
    <div class="col-md-6">
        <dl class="row">
            <dt class="col-sm-4">Billing ID</dt><dd class="col-sm-8">{{ $customer->billing_id }}</dd>
            <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $customer->customers_email_address }}</dd>
            <dt class="col-sm-4">Backup Email</dt><dd class="col-sm-8">{{ $customer->backup_email_address }}</dd>
            <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $customer->customers_telephone }}</dd>
            <dt class="col-sm-4">Cell</dt><dd class="col-sm-8">{{ $customer->customers_fax }}</dd>
        </dl>
    </div>
    <div class="col-md-6">
        <h5>Addresses</h5>
        <p><strong>Billing:</strong> {{ $customer->defaultAddress?->full ?? 'N/A' }}</p>
        <p><strong>Shipping:</strong> {{ $customer->shippingAddress?->full ?? 'N/A' }}</p>
        <p><strong>Emergency:</strong> {{ $customer->emergencyAddress?->full ?? 'N/A' }}</p>
    </div>
</div>

@if($userIsManager)
    <div class="mb-3">
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/contact-info" class="btn btn-sm btn-outline-secondary">Edit Contact</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/payment-info" class="btn btn-sm btn-outline-secondary">Edit Payment</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/default-addresses" class="btn btn-sm btn-outline-secondary">Edit Addresses</a>
        <a href="/{{ $prefix }}/orders/add/{{ $customer->customers_id }}" class="btn btn-sm btn-outline-primary">New Order</a>
        <a href="/{{ $prefix }}/customer/{{ $customer->customers_id }}/request/add" class="btn btn-sm btn-outline-primary">New Request</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/close-account" class="btn btn-sm btn-outline-danger" onclick="return confirm('Close this account?')">Close Account</a>
    </div>
@endif

<h4>Authorized Names</h4>
@if($customer->authorizedNames->isNotEmpty())
    <ul>
        @foreach($customer->authorizedNames as $name)
            <li>{{ $name->authorized_firstname }} {{ $name->authorized_lastname }}
                <a href="/{{ $prefix }}/authorized_names/{{ $name->authorized_names_id }}/edit">Edit</a> |
                <a href="/{{ $prefix }}/authorized_names/{{ $name->authorized_names_id }}/delete" onclick="return confirm('Delete?')">Delete</a>
            </li>
        @endforeach
    </ul>
@endif
<form method="POST" action="/{{ $prefix }}/customers/{{ $customer->customers_id }}/authorized_names/add" class="row g-2 mb-3">
    @csrf
    <div class="col-auto"><input type="text" name="authorized_firstname" class="form-control form-control-sm" placeholder="First name" required></div>
    <div class="col-auto"><input type="text" name="authorized_lastname" class="form-control form-control-sm" placeholder="Last name" required></div>
    <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Add Name</button></div>
</form>

<h4>Orders</h4>
@if($orders->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Order #</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                        <td><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></td>
                        <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                        <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $orders->appends(request()->query())->links() }}
@else
    <p>No orders.</p>
@endif

@if($customRequests->isNotEmpty())
    <h4>Custom Requests</h4>
    <table class="table table-sm">
        <thead><tr><th>Date</th><th>Description</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($customRequests as $req)
                <tr>
                    <td>{{ $req->request_date?->format('m/d/Y') }}</td>
                    <td>{{ $req->description }}</td>
                    <td>{{ $req->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
