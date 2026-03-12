@extends('layouts.manager')
@section('title', 'Custom Requests - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Custom Package Requests</h2>
<form method="GET" action="/{{ $prefix }}/requests" class="row g-3 mb-4">
    <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search..." value="{{ $search }}"></div>
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
@if($requests->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead><tr><th>Date</th><th>Customer</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->request_date?->format('m/d/Y') }}</td>
                        <td>
                            @if($req->customer)
                                <a href="/{{ $prefix }}/customers/view/{{ $req->customer->customers_id }}">{{ $req->customer->full_name }}</a>
                            @endif
                        </td>
                        <td>{{ Str::limit($req->description, 60) }}</td>
                        <td>{{ $statusFilterOptions[$req->status] ?? $req->status }}</td>
                        <td>
                            <a href="/{{ $prefix }}/requests/edit/{{ $req->custom_package_requests_id }}">Edit</a> |
                            <a href="/{{ $prefix }}/requests/delete/{{ $req->custom_package_requests_id }}" onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $requests->appends(request()->query())->links() }}
@endif
@endsection
