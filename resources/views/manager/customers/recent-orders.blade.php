@extends('layouts.manager')
@section('title', 'Recent Orders - ' . $customerName)
@section('content')

<x-page-header title="Recent Orders - {{ $customerName }}" />

<x-table-card title="Orders">
    <table class="table table-modern">
        <thead><tr><th>Order #</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($orders as $order)
                @php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
                <tr>
                    <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                    <td><x-status-badge :status="$order->status?->orders_status_name" /></td>
                    <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-table-card>

{{ $orders->links() }}
@endsection
