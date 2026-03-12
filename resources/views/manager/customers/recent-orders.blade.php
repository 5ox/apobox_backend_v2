@extends('layouts.manager')
@section('title', 'Recent Orders - ' . $customerName)
@section('content')
<h2>Recent Orders - {{ $customerName }}</h2>
<div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead><tr><th>Order #</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($orders as $order)
                @php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
                <tr>
                    <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                    <td><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></td>
                    <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{ $orders->links() }}
@endsection
