@extends('layouts.manager')
@section('title', $customer->full_name . ' - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="{{ $customer->full_name }}">
    <x-slot:actions>
        <div class="d-flex align-items-center gap-2">
            @if($closed)
                <x-status-badge status="Closed {{ $closed }}" />
            @endif
            @if($customer->billing_id)
                <span class="badge bg-primary fs-5 px-3 py-2">{{ $customer->billing_id }}</span>
            @endif
        </div>
    </x-slot:actions>
</x-page-header>

@if($partialSignup) <div class="alert alert-warning"><i data-lucide="alert-triangle" class="icon--sm me-1"></i>Partial signup - no billing ID</div> @endif

<div class="row">
    <div class="col-md-6">
        <x-detail-card title="Customer Information">
            <x-detail-row label="Billing ID">{{ $customer->billing_id }}</x-detail-row>
            <x-detail-row label="Email">{{ $customer->customers_email_address }}</x-detail-row>
            <x-detail-row label="Backup Email">{{ $customer->backup_email_address }}</x-detail-row>
            <x-detail-row label="Phone">{{ $customer->customers_telephone }}</x-detail-row>
            <x-detail-row label="Cell">{{ $customer->customers_fax }}</x-detail-row>
        </x-detail-card>
    </div>
    <div class="col-md-6">
        <div class="row g-3">
            <div class="col-12">
                <x-address-card label="Billing Address">{{ $customer->defaultAddress?->full ?? '' }}</x-address-card>
            </div>
            <div class="col-12">
                <x-address-card label="Shipping Address">{{ $customer->shippingAddress?->full ?? '' }}</x-address-card>
            </div>
            <div class="col-12">
                <x-address-card label="Emergency Address">{{ $customer->emergencyAddress?->full ?? '' }}</x-address-card>
            </div>
        </div>
    </div>
</div>

@if($userIsManager)
    <div class="action-bar mt-3">
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/contact-info" class="btn btn-sm btn-outline-secondary"><i data-lucide="pencil" class="icon--sm me-1"></i>Edit Contact</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/payment-info" class="btn btn-sm btn-outline-secondary"><i data-lucide="credit-card" class="icon--sm me-1"></i>Edit Payment</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/edit/default-addresses" class="btn btn-sm btn-outline-secondary"><i data-lucide="map-pin" class="icon--sm me-1"></i>Edit Addresses</a>
        <a href="/{{ $prefix }}/orders/add/{{ $customer->customers_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="plus" class="icon--sm me-1"></i>New Order</a>
        <a href="/{{ $prefix }}/customer/{{ $customer->customers_id }}/request/add" class="btn btn-sm btn-outline-primary"><i data-lucide="plus" class="icon--sm me-1"></i>New Request</a>
        <a href="/{{ $prefix }}/customers/{{ $customer->customers_id }}/close-account" class="btn btn-sm btn-outline-danger" onclick="return confirm('Close this account?')"><i data-lucide="x-circle" class="icon--sm me-1"></i>Close Account</a>
    </div>
@endif

<x-table-card title="Authorized Names" class="mt-4">
    @if($customer->authorizedNames->isNotEmpty())
        <table class="table table-modern">
            <thead><tr><th>Name</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($customer->authorizedNames as $name)
                    <tr>
                        <td>{{ $name->authorized_firstname }} {{ $name->authorized_lastname }}</td>
                        <td>
                            <a href="/{{ $prefix }}/authorized_names/{{ $name->authorized_names_id }}/edit" class="btn btn-sm btn-outline-secondary"><i data-lucide="pencil" class="icon--sm"></i></a>
                            <a href="/{{ $prefix }}/authorized_names/{{ $name->authorized_names_id }}/delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i data-lucide="trash-2" class="icon--sm"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted px-3 py-2">No authorized names.</p>
    @endif
    <x-slot:footer>
        <form method="POST" action="/{{ $prefix }}/customers/{{ $customer->customers_id }}/authorized_names/add" class="row g-2 align-items-end">
            @csrf
            <div class="col-auto"><input type="text" name="authorized_firstname" class="form-control form-control-sm" placeholder="First name" required></div>
            <div class="col-auto"><input type="text" name="authorized_lastname" class="form-control form-control-sm" placeholder="Last name" required></div>
            <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary"><i data-lucide="plus" class="icon--sm me-1"></i>Add Name</button></div>
        </form>
    </x-slot:footer>
</x-table-card>

<x-table-card title="Orders" class="mt-4">
    @if($orders->isNotEmpty())
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Order #</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                            <td><x-status-badge :status="$order->status?->orders_status_name" /></td>
                            <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                            <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>{{ $orders->appends(request()->query())->links() }}</x-slot:footer>
    @else
        <p class="text-muted px-3 py-2">No orders.</p>
    @endif
</x-table-card>

@if($customRequests->isNotEmpty())
    <x-table-card title="Custom Requests" class="mt-4">
        <table class="table table-modern">
            <thead><tr><th>Date</th><th>Instructions</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($customRequests as $req)
                    <tr>
                        <td>{{ $req->order_add_date?->format('m/d/Y') }}</td>
                        <td>{{ $req->instructions }}</td>
                        <td><x-status-badge :status="$req->status_label" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-table-card>
@endif

@if(!empty($zendeskTickets))
    <x-table-card title="Support Tickets" class="mt-4">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Ticket</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zendeskTickets as $ticket)
                    <tr>
                        <td><a href="{{ $ticket['url'] }}" target="_blank">#{{ $ticket['id'] }}</a></td>
                        <td>{{ $ticket['subject'] }}</td>
                        <td>
                            @php
                                $statusColors = ['new' => 'info', 'open' => 'primary', 'pending' => 'warning', 'hold' => 'secondary', 'solved' => 'success', 'closed' => 'dark'];
                                $color = $statusColors[$ticket['status']] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($ticket['status']) }}</span>
                        </td>
                        <td>{{ $ticket['updated_at'] ? \Carbon\Carbon::parse($ticket['updated_at'])->format('m/d/Y g:ia') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-table-card>
@endif
@endsection
