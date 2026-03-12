@extends('layouts.manager')
@section('title', 'Search Customers - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Customers</h2>
<form method="GET" action="/{{ $prefix }}/customers" class="row g-3 mb-4">
    <div class="col-auto">
        <input type="text" name="q" class="form-control" placeholder="Search customers..." value="{{ $search }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
</form>
@if($results instanceof \Illuminate\Pagination\LengthAwarePaginator && $results->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Billing ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($results as $result)
                    @php $customer = $result->customer ?? $result; @endphp
                    <tr>
                        <td>{{ $customer->billing_id ?? '' }}</td>
                        <td>{{ $customer->full_name ?? $customer->customers_firstname . ' ' . $customer->customers_lastname }}</td>
                        <td>{{ $customer->customers_email_address ?? '' }}</td>
                        <td><a href="/{{ $prefix }}/customers/view/{{ $customer->customers_id }}">View</a></td>
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
