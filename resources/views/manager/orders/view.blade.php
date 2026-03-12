@extends('layouts.manager')
@section('title', 'Order #' . $order->orders_id . ' - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Order #{{ $order->orders_id }}</h2>

<div class="row">
    <div class="col-md-6">
        <dl class="row">
            <dt class="col-sm-4">Customer</dt><dd class="col-sm-8"><a href="/{{ $prefix }}/customers/view/{{ $order->customer?->customers_id }}">{{ $order->customer?->full_name }}</a></dd>
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></dd>
            <dt class="col-sm-4">Date</dt><dd class="col-sm-8">{{ $order->date_purchased?->format('m/d/Y g:i A') }}</dd>
            <dt class="col-sm-4">Mail Class</dt><dd class="col-sm-8">{{ $order->mail_class }}</dd>
            <dt class="col-sm-4">Outbound</dt><dd class="col-sm-8">{{ $order->usps_track_num ?: 'N/A' }}</dd>
            <dt class="col-sm-4">Inbound</dt><dd class="col-sm-8">{{ $order->inbound_tracking ?: 'N/A' }}</dd>
            <dt class="col-sm-4">Dimensions</dt><dd class="col-sm-8">{{ $order->dimensions }}</dd>
            <dt class="col-sm-4">Weight</dt><dd class="col-sm-8">{{ $order->weight }} lb</dd>
            @if($creator)<dt class="col-sm-4">Created by</dt><dd class="col-sm-8">{{ $creator }}</dd>@endif
        </dl>
    </div>
    <div class="col-md-6">
        {{-- Update Status Form --}}
        <form method="POST" action="/{{ $prefix }}/orders/{{ $order->orders_id }}/update-status">
            @csrf
            <div class="mb-2">
                <select name="orders_status" class="form-select form-select-sm">
                    @foreach($ordersStatuses as $id => $name)
                        <option value="{{ $id }}" @selected($order->orders_status == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2"><textarea name="status_history_comments" class="form-control form-control-sm" rows="2" placeholder="Comments"></textarea></div>
            <div class="mb-2"><input type="text" name="usps_track_num" class="form-control form-control-sm" placeholder="Outbound tracking number" value="{{ $order->usps_track_num }}"></div>
            <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="notify_customer" id="notifyCustomer" value="1" checked><label class="form-check-label" for="notifyCustomer">Notify Customer</label></div>
            <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
        </form>

        <div class="mt-3">
            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/charge" class="btn btn-sm btn-outline-success">Charge</a>
            @if($mailClass === 'usps')
                <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/print_label" class="btn btn-sm btn-outline-secondary">Print USPS Label</a>
            @else
                <a href="{{ $url }}" class="btn btn-sm btn-outline-secondary">{{ $action }} FedEx Label</a>
            @endif
            <a href="/{{ $prefix }}/orders/{{ $order->orders_id }}/delete_label" class="btn btn-sm btn-outline-warning" onclick="return confirm('Delete label?')">Delete Label</a>
            <a href="/{{ $prefix }}/orders/delete/{{ $order->orders_id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this order?')">Delete Order</a>
        </div>
    </div>
</div>

<h4 class="mt-4">Order Charges</h4>
<table class="table table-sm">
    <tbody>
        @foreach($orderCharges as $charge)
            <tr @if(in_array($charge->class, ['ot_subtotal', 'ot_total'])) class="fw-bold" @endif>
                <td>{{ $charge->title }}</td>
                <td class="text-end">${{ number_format($charge->value, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<h4>Status History</h4>
<table class="table table-sm table-striped">
    <thead><tr><th>Date</th><th>Status</th><th>Comments</th></tr></thead>
    <tbody>
        @foreach($statusHistories as $history)
            <tr>
                <td>{{ $history->date_added?->format('m/d/Y g:i A') }}</td>
                <td>{{ $history->status?->orders_status_name }}</td>
                <td>{{ $history->comments }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
