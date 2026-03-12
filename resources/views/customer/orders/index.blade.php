@extends('layouts.default')
@section('title', 'My Orders - APO Box')
@section('content')
<h2>My Orders</h2>
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
    {{ $orders->links() }}
@endif
@endsection
