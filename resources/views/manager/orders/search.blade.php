@extends('layouts.manager')
@section('title', 'Search Orders - APO Box Admin')
@section('module', 'search')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Orders" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/orders" class="row g-3 align-items-end" id="order-search-form">
        <div class="col-md-5 col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i data-lucide="search" class="icon--sm"></i></span>
                <input type="text" name="q" id="search-input" class="form-control"
                       placeholder="Order #, tracking, customer name..."
                       value="{{ $search }}" autocomplete="off"
                       data-search-url="/{{ $prefix }}/orders"
                       data-search-type="orders">
            </div>
            <div class="form-text">Search by order #, tracking number, customer name, or billing ID</div>
        </div>
        <div class="col-auto">
            <select name="showStatus" id="status-filter" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statusFilterOptions as $id => $name)
                    <option value="{{ $id }}" @selected($showStatus == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon--sm me-1"></i>Search</button>
        </div>
    </form>
</x-form-section>

{{-- Live search results container --}}
<div id="live-results" class="d-none">
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
                <tbody id="live-results-body"></tbody>
            </table>
        </div>
        <x-slot:footer><div id="live-results-info" class="text-muted small"></div></x-slot:footer>
    </x-table-card>
</div>

{{-- Server-rendered results --}}
<div id="static-results">
@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach($results as $order)
                        <tr>
                            <td><a href="/{{ $prefix }}/orders/{{ $order->orders_id }}" class="fw-semibold">{{ $order->orders_id }}</a></td>
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
@elseif($search || $showStatus)
    <div class="text-center py-5 text-muted">
        <i data-lucide="search-x" style="width:48px;height:48px" class="mb-3 opacity-50"></i>
        <p>No orders found{{ $search ? ' for "' . e($search) . '"' : '' }}</p>
    </div>
@endif
</div>
@endsection
