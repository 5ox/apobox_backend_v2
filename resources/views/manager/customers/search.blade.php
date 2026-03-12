@extends('layouts.manager')
@section('title', 'Search Customers - APO Box Admin')
@section('module', 'search')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Customer Search" />

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
            <div class="form-text">Search by name, billing ID, email, or phone number</div>
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

{{-- Server-rendered results (shown on full page load, hidden during live search) --}}
<div id="static-results">
@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Billing ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($results as $result)
                        @php $customer = $result->customer ?? $result; @endphp
                        <tr>
                            <td><span class="fw-semibold font-monospace">{{ $customer->billing_id ?? '' }}</span></td>
                            <td>{{ $customer->full_name ?? $customer->customers_firstname . ' ' . $customer->customers_lastname }}</td>
                            <td class="text-muted">{{ $customer->customers_email_address ?? '' }}</td>
                            <td><a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="eye" class="icon--sm me-1"></i>View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>{{ $results->appends(request()->query())->links() }}</x-slot:footer>
    </x-table-card>
@elseif($search)
    <div class="text-center py-5 text-muted">
        <i data-lucide="search-x" style="width:48px;height:48px" class="mb-3 opacity-50"></i>
        <p>No customers found for "<strong>{{ $search }}</strong>"</p>
    </div>
@endif
</div>
@endsection
