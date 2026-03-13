@extends('layouts.manager')
@section('title', ($search ? 'Search: ' . $search : 'All Customers') . ' - APO Box Admin')
@section('module', 'search')
@section('content')
@php
    $prefix = auth('admin')->user()->routePrefix();
    $sortCol = $sortCol ?? 'customers_lastname';
    $sortDir = $sortDir ?? 'asc';
    $nextDir = $sortDir === 'asc' ? 'desc' : 'asc';
@endphp
<x-page-header title="{{ $search ? 'Customer Search' : 'All Customers' }}" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/customers" class="row g-3" id="customer-search-form">
        <div class="col-md-6 col-lg-4">
            <div class="input-group">
                <span class="input-group-text"><i data-lucide="search" class="icon--sm"></i></span>
                <input type="text" name="q" id="search-input" class="form-control"
                       placeholder="Name, billing ID, email, phone..."
                       value="{{ $search }}" autocomplete="off"
                       data-search-url="/{{ $prefix }}/customers"
                       data-search-type="customers">
                <button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon--sm me-1"></i>Search</button>
            </div>
            @if($search)
                <div class="form-text"><a href="/{{ $prefix }}/customers" class="text-decoration-none">Clear search</a></div>
            @else
                <div class="form-text">Search by name, billing ID, email, or phone number</div>
            @endif
        </div>
    </form>
</x-form-section>

{{-- Live search results container (hidden by default, shown by JS) --}}
<div id="live-results" class="d-none">
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Billing ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                <tbody id="live-results-body"></tbody>
            </table>
        </div>
        <x-slot:footer><div id="live-results-info" class="text-muted small"></div></x-slot:footer>
    </x-table-card>
</div>

{{-- Server-rendered results --}}
<div id="static-results">
@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    @php
        $sortUrl = function ($col) use ($prefix, $search, $sortCol, $sortDir) {
            $dir = ($sortCol === $col && $sortDir === 'asc') ? 'desc' : 'asc';
            $params = ['sort' => $col, 'dir' => $dir];
            if ($search) $params['q'] = $search;
            return '/' . $prefix . '/customers?' . http_build_query($params);
        };
        $sortIcon = function ($col) use ($sortCol, $sortDir) {
            if ($sortCol !== $col) return '';
            return $sortDir === 'asc' ? ' ↑' : ' ↓';
        };
    @endphp
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th><a href="{{ $sortUrl('billing_id') }}" class="text-decoration-none text-dark">Billing ID{!! $sortIcon('billing_id') !!}</a></th>
                        <th><a href="{{ $sortUrl('customers_lastname') }}" class="text-decoration-none text-dark">Name{!! $sortIcon('customers_lastname') !!}</a></th>
                        <th><a href="{{ $sortUrl('customers_email_address') }}" class="text-decoration-none text-dark">Email{!! $sortIcon('customers_email_address') !!}</a></th>
                        <th><a href="{{ $sortUrl('is_active') }}" class="text-decoration-none text-dark">Status{!! $sortIcon('is_active') !!}</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $customer)
                        @php $c = $customer->customer ?? $customer; @endphp
                        <tr>
                            <td><span class="fw-semibold font-monospace">{{ $c->billing_id ?? '' }}</span></td>
                            <td>{{ $c->full_name ?? $c->customers_firstname . ' ' . $c->customers_lastname }}</td>
                            <td class="text-muted">{{ $c->customers_email_address ?? '' }}</td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Closed</span>
                                @endif
                            </td>
                            <td><a href="/{{ $prefix }}/customers/view/{{ $c->customers_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="eye" class="icon--sm me-1"></i>View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted small">{{ $results->total() }} {{ Str::plural('customer', $results->total()) }}</span>
                {{ $results->links() }}
            </div>
        </x-slot:footer>
    </x-table-card>
@elseif($search)
    <div class="text-center py-5 text-muted">
        <i data-lucide="search-x" style="width:48px;height:48px" class="mb-3 opacity-50"></i>
        <p>No customers found for "<strong>{{ $search }}</strong>"</p>
    </div>
@endif
</div>
@endsection
