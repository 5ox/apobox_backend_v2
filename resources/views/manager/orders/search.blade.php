@extends('layouts.manager')
@section('title', 'Search Orders - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Orders" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/orders" class="row g-3">
        <div class="col-auto">
            <div class="input-group">
                <span class="input-group-text"><i data-lucide="search" class="icon--sm"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Search orders..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-auto">
            <select name="showStatus" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statusFilterOptions as $id => $name)
                    <option value="{{ $id }}" @selected($showStatus == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon--sm me-1"></i>Search</button></div>
    </form>
</x-form-section>

@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
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
                            <td><x-status-badge :status="$order->status?->orders_status_name" /></td>
                            <td>${{ number_format($order->total?->value ?? 0, 2) }}</td>
                            <td>{{ $order->date_purchased?->format('m/d/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>{{ $results->appends(request()->query())->links() }}</x-slot:footer>
    </x-table-card>
@elseif($search)
    <p class="text-muted">No results found.</p>
@endif
@endsection
