@extends('layouts.manager')
@section('title', 'Customer Info Report - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Customer Info Report</h2>
<form method="GET" action="/{{ $prefix }}/customers/report" class="row g-3 mb-4">
    <div class="col-auto">
        <select name="interval" class="form-select">
            @foreach($validIntervals as $int)
                <option value="{{ $int }}" @selected($interval == $int)>{{ ucfirst($int) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <select name="sort_field" class="form-select">
            @foreach($validSortFields as $field)
                <option value="{{ $field }}" @selected(request('sort_field') == $field)>{{ str_replace('customers_info_', '', $field) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div>
    <div class="col-auto"><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Run</button></div>
</form>
@if($results && $results->isNotEmpty())
    <table class="table table-sm table-striped">
        <thead><tr><th>Period</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($results as $row)
                <tr><td>{{ $row->period }}</td><td>{{ $row->count }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
