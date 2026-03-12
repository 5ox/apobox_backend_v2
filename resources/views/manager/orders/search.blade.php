@extends('layouts.manager')
@section('title', 'Search Orders - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Orders</h2>
<form method="GET" action="/{{ $prefix }}/orders" class="row g-3 mb-4">
    <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search orders..." value="{{ $search }}"></div>
    <div class="col-auto">
        <select name="showStatus" class="form-select">
            <option value="">All Statuses</option>
            @foreach($statusFilterOptions as $id => $name)
                <option value="{{ $id }}" @selected($showStatus == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Search</button></div>
</form>
@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($results as $order)
                    <tr>
                        <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}">{{ $order->orders_id }}</a></td>
                        <td>
                            @if($order->customer)
                                <a href="/{{ $prefix }}/customers/view/{{ $order->customer->customers_id }}">{{ $order->customer->full_name }}</a>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $order->status?->orders_status_name }}</span></td>
                        <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                        <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $results->appends(request()->query())->links() }}
@elseif($search)
    <p>No results found.</p>
@endif
@endsection
