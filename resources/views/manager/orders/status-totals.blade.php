@extends('layouts.manager')
@section('title', 'Order Status Totals - APO Box Admin')
@section('content')
<h2>Order Status Totals</h2>
<table class="table table-sm">
    <thead><tr><th>Status</th><th>Count</th></tr></thead>
    <tbody>
        @foreach($statuses as $status)
            <tr>
                <td>{{ $status->orders_status_name }}</td>
                <td>{{ $statusCounts[$status->orders_status_id] ?? 0 }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
