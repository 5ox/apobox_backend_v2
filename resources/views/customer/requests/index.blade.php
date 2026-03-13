@extends('layouts.default')
@section('title', 'My Requests - APO Box')
@section('content')
<x-page-header title="Custom Package Requests">
    <x-slot:actions>
        <a href="{{ url('/requests/add') }}" class="btn btn-sm btn-primary"><i data-lucide="plus" class="icon--sm"></i> New Request</a>
    </x-slot:actions>
</x-page-header>

@if($requests->isEmpty())
    <div class="text-center py-5 text-muted">
        <i data-lucide="package" style="width:48px;height:48px" class="mb-3 text-secondary"></i>
        <p class="mb-1">You have no custom package requests.</p>
        <p><a href="{{ url('/requests/add') }}">Create your first request</a> to get started.</p>
    </div>
@else
    <x-table-card>
        <div class="table-responsive">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Tracking #</th>
                        <th>Services</th>
                        <th>Instructions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td class="text-nowrap">{{ $request->order_add_date?->format('m/d/Y') }}</td>
                            <td>
                                @if($request->tracking_id && $request->tracking_id !== '0')
                                    <code>{{ $request->tracking_id }}</code>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($request->package_repack === 'yes')
                                    <span class="badge bg-info-subtle text-info">Repack</span>
                                @endif
                                @if($request->insurance_coverage)
                                    <span class="badge bg-warning-subtle text-warning">${{ $request->insurance_coverage }} Ins.</span>
                                @endif
                                @if($request->package_repack !== 'yes' && !$request->insurance_coverage)
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($request->instructions, 60) }}</td>
                            <td><x-status-badge :status="$request->status_label ?? 'Unknown'" /></td>
                            <td class="text-nowrap">
                                <a href="{{ url('/requests/edit/' . $request->custom_orders_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i data-lucide="pencil" class="icon--sm"></i> Edit
                                </a>
                                @if(!$request->orders_id || $request->orders_id === '0')
                                    <a href="{{ url('/requests/delete/' . $request->custom_orders_id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this request?')">
                                        <i data-lucide="trash-2" class="icon--sm"></i>
                                    </a>
                                @endif
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
