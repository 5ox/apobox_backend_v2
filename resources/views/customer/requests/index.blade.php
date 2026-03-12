@extends('layouts.default')
@section('title', 'My Requests - APO Box')
@section('content')
<x-page-header title="Custom Package Requests">
    <x-slot:actions>
        <a href="{{ url('/requests/add') }}" class="btn btn-sm btn-primary"><i data-lucide="plus" class="icon--sm"></i> New Request</a>
    </x-slot:actions>
</x-page-header>

@if($requests->isEmpty())
    <p class="text-muted">You have no custom package requests.</p>
@else
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead><tr><th>Date</th><th>Instructions</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->order_add_date?->format('m/d/Y') }}</td>
                            <td>{{ $request->instructions }}</td>
                            <td>{{ $request->status_label ?? $request->package_status }}</td>
                            <td>
                                <a href="{{ url('/requests/edit/' . $request->custom_orders_id) }}">Edit</a> |
                                <a href="{{ url('/requests/delete/' . $request->custom_orders_id) }}" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-slot:footer>
            {{ $requests->links() }}
        </x-slot:footer>
    </x-table-card>
@endif
@endsection
