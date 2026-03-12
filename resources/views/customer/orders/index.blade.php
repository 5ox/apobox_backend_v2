@extends('layouts.default')
@section('title', 'My Orders - APO Box')
@section('content')
<x-page-header title="My Orders" />
@if($orders->isEmpty())
    <p class="text-muted">You have no orders at this time.</p>
@else
    <x-table-card>
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
        <x-slot:footer>
            {{ $orders->links() }}
        </x-slot:footer>
    </x-table-card>
@endif
@endsection
