@extends('layouts.default')
@section('title', 'Order #' . $order->orders_id . ' - APO Box')
@section('content')
<h2>Order #{{ $order->orders_id }}</h2>
<div class="row">
    <div class="col-md-6">
        <dl class="row">
            <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></dd>
            <dt class="col-sm-4">Date Purchased</dt><dd class="col-sm-8">{{ $order->date_purchased?->format('m/d/Y g:i A') }}</dd>
            <dt class="col-sm-4">Date Shipped</dt><dd class="col-sm-8">{{ $order->date_shipped?->format('m/d/Y') ?? 'N/A' }}</dd>
            <dt class="col-sm-4">Postage Class</dt><dd class="col-sm-8">{{ $order->mail_class }}</dd>
            <dt class="col-sm-4">Outbound Tracking</dt><dd class="col-sm-8">{{ $order->usps_track_num ?: 'N/A' }}</dd>
            <dt class="col-sm-4">Inbound Tracking</dt><dd class="col-sm-8">{{ $order->inbound_tracking ?: 'N/A' }}</dd>
            <dt class="col-sm-4">Dimensions</dt><dd class="col-sm-8">{{ $order->dimensions }}</dd>
            <dt class="col-sm-4">Weight</dt><dd class="col-sm-8">{{ $order->weight }} lb</dd>
        </dl>
    </div>
</div>
<h4>Order Charges</h4>
<div class="table-responsive">
    <table class="table table-sm">
        <thead><tr><th>Description</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
            @foreach($orderCharges as $charge)
                <tr @if(in_array($charge->class, ['ot_subtotal', 'ot_total'])) class="fw-bold" @endif>
                    <td>{{ $charge->title }}</td>
                    <td class="text-end">${{ number_format($charge->value, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($order->statusHistory->isNotEmpty())
    <h4>Status History</h4>
    <table class="table table-sm">
        <thead><tr><th>Date</th><th>Status</th><th>Comments</th></tr></thead>
        <tbody>
            @foreach($order->statusHistory as $history)
                <tr>
                    <td>{{ $history->date_added?->format('m/d/Y g:i A') }}</td>
                    <td>{{ $history->status?->orders_status_name }}</td>
                    <td>{{ $history->comments }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
<a href="{{ url('/orders') }}" class="btn btn-secondary">Back to Orders</a>
@endsection
