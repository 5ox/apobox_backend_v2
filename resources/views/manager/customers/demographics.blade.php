@extends('layouts.manager')
@section('title', 'Demographics Report - APO Box Admin')
@section('content')
@php $prefix = auth('admin')->user()->role === 'manager' ? 'manager' : 'employee'; @endphp
<h2>Demographics Report</h2>
<form method="GET" action="/{{ $prefix }}/customers/demographics" class="row g-3 mb-4">
    <div class="col-auto">
        <select name="field" class="form-select">
            @foreach($reportFields as $key => $label)
                <option value="{{ $key }}" @selected($options['field'] == $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><input type="number" name="limit" class="form-control" placeholder="Limit" value="{{ $options['limit'] }}"></div>
    <div class="col-auto"><input type="date" name="from_date" class="form-control" value="{{ $options['from_date'] }}"></div>
    <div class="col-auto"><input type="date" name="to_date" class="form-control" value="{{ $options['to_date'] }}"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Run Report</button></div>
</form>
@if($data->isNotEmpty())
    <table class="table table-sm table-striped">
        <thead><tr><th>{{ $reportFields[$options['field']] ?? 'Label' }}</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($data as $row)
                <tr><td>{{ $row->label }}</td><td>{{ $row->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
