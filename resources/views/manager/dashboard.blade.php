@extends('layouts.manager')
@section('title', 'Dashboard - APO Box Admin')
@section('content')
<h2>Dashboard</h2>
<div class="row">
    <div class="col-md-6">
        <h4>Paid Manually (Recent 10)</h4>
        @if($paidManually->isEmpty())
            <p class="text-muted">None</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($paidManually as $order)
                            @php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
                            <tr>
                                <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                                <td>{{ $order->customer?->full_name }}</td>
                                <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                                <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <h4>In Warehouse (Recent 10)</h4>
        @if($inWarehouse->isEmpty())
            <p class="text-muted">None</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($inWarehouse as $order)
                            @php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
                            <tr>
                                <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                                <td>{{ $order->customer?->full_name }}</td>
                                <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                                <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
