@extends('layouts.manager')
@section('title', 'Custom Requests - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp

<x-page-header title="Custom Package Requests" />

<x-form-section>
    <form method="GET" action="/{{ $prefix }}/requests" class="row g-3">
        <div class="col-auto"><input type="text" name="q" class="form-control" placeholder="Search..." value="{{ $search }}"></div>
        <div class="col-auto">
            <select name="showStatus" class="form-select">
                <option value="">All Statuses</option>
                @foreach($statusFilterOptions as $id => $name)
                    <option value="{{ $id }}" @selected($showStatus == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-primary"><i data-lucide="search" class="icon-sm me-1"></i>Search</button></div>
    </form>
</x-form-section>

@if($requests->isNotEmpty())
    <x-table-card>
        <div class="table-responsive">
            <table class="table-modern">
                <thead><tr><th>Date</th><th>Customer</th><th>Instructions</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td>{{ $req->order_add_date?->format('m/d/Y') }}</td>
                            <td>
                                @if($req->customer)
                                    <a href="/{{ $prefix }}/customers/view/{{ $req->customer->customers_id }}">{{ $req->customer->full_name }}</a>
                                @endif
                            </td>
                            <td>{{ Str::limit($req->instructions, 60) }}</td>
                            <td><x-status-badge :status="$statusFilterOptions[$req->package_status] ?? $req->package_status" /></td>
                            <td>
                                <a href="/{{ $prefix }}/requests/edit/{{ $req->custom_orders_id }}" class="btn btn-sm btn-outline-primary"><i data-lucide="pencil" class="icon-sm"></i></a>
                                <a href="/{{ $prefix }}/requests/delete/{{ $req->custom_orders_id }}" onclick="return confirm('Delete?')" class="btn btn-sm btn-outline-danger"><i data-lucide="trash-2" class="icon-sm"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>
            {{ $requests->appends(request()->query())->links() }}
        </x-slot:footer>
    </x-table-card>
@endif
@endsection
