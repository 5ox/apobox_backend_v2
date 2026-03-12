@extends('layouts.manager')
@section('title', 'Search Customers - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<x-page-header title="Customer Search" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/customers" class="row g-3">
        <div class="col-auto">
            <div class="input-group">
                <span class="input-group-text"><i data-lucide="search" class="icon--sm"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Search customers..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon--sm me-1"></i>Search</button>
        </div>
    </form>
</x-form-section>

@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Billing ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($results as $result)
                        @php $customer = $result->customer ?? $result; @endphp
                        <tr>
                            <td>{{ $customer->billing_id ?? '' }}</td>
                            <td>{{ $customer->full_name ?? $customer->customers_firstname . ' ' . $customer->customers_lastname }}</td>
                            <td>{{ $customer->customers_email_address ?? '' }}</td>
                            <td><a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="eye" class="icon--sm me-1"></i>View</a></td>
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
