@extends('layouts.manager')
@section('title', 'Order Report - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Order Report</h2>
<form method="GET" action="/{{ $prefix }}/orders/report" class="row g-3 mb-4">
    <div class="col-auto">
        <select name="interval" class="form-select">
            @foreach($validIntervals as $key => $label)
                <option value="{{ $key }}" @selected($interval == $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="showStatus" class="form-select">
            <option value="">All Statuses</option>
            @foreach($statusFilterOptions as $id => $name)
                <option value="{{ $id }}" @selected(request('showStatus') == $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
    <div class="col-auto"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Run</button></div>
</form>
@if($results->isNotEmpty())
    <table class="table table-sm table-striped">
        <thead><tr><th>Period</th><th>Total Orders</th><th>Paid Orders</th></tr></thead>
        <tbody>
            @foreach($results as $row)
                <tr><td>{{ $row->period }}</td><td>{{ $row->total_orders }}</td><td>{{ $row->paid_orders }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
