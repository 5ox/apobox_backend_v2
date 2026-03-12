@extends('layouts.manager')
@section('title', 'Order Status Totals - APO Box Admin')
@section('content')

<x-page-header title="Order Status Totals" />

<x-table-card>
    <table class="table-modern">
        <thead><tr><th>Status</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($statuses as $status)
                <tr>
                    <td><x-status-badge :status="$status->orders_status_name" /></td>
                    <td>{{ $statusCounts[$status->orders_status_id] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-table-card>
@endsection
